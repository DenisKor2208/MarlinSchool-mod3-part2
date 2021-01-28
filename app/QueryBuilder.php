<?php

namespace App;

use Aura\SqlQuery\QueryFactory;
use PDO;

Class QueryBuilder {

    private $pdo, $queryFactory;

    public function __construct(PDO $pdo, QueryFactory $queryFactory)
    {
        $this->pdo = $pdo;
        $this->queryFactory = $queryFactory;
    }

    public function getOne($table, $id)
    {
        $queryFactory = $this->queryFactory;
        $select = $queryFactory->newSelect();
        $select->cols(['*'])
               ->from($table)
               ->where('id = :id')
               ->bindValue('id', $id);

        $pdo = $this->pdo;
        $sth = $pdo->prepare($select->getStatement());
        $sth->execute($select->getBindValues());
        $results = $sth->fetchAll(PDO::FETCH_ASSOC);

        return $results;
    }

    public function getAll($table)
    {
        $queryFactory = $this->queryFactory;
        $select = $queryFactory->newSelect();
        $select->cols(['*']) -> from($table);

        $pdo = $this->pdo;
        $sth = $pdo->prepare($select->getStatement());
        $sth->execute($select->getBindValues());
        $results = $sth->fetchAll(PDO::FETCH_ASSOC);

        return $results;
    }

    public function insert($data, $table)
    {
        $queryFactory = $this->queryFactory;
        $insert = $queryFactory->newInsert();
        $insert
            ->into($table)
            ->cols($data);

        $pdo = $this->pdo;
        $sth = $pdo->prepare($insert->getStatement());
        $sth->execute($insert->getBindValues());
    }

    public function update($data, $id, $table)
    {
        $queryFactory = $this->queryFactory;
        $update = $queryFactory->newUpdate();
        $update
            ->table($table)
            ->cols($data)
            ->where('id = :id')
            ->bindValue('id', $id);

        $pdo = $this->pdo;
        $sth = $pdo->prepare($update->getStatement());
        $sth->execute($update->getBindValues());
    }

    public function delete($table, $id)
    {
        $queryFactory = $this->queryFactory;
        $delete = $queryFactory->newDelete();

        $delete
            ->from($table)
            ->where('id = :id')
            ->bindValue('id', $id);

        $pdo = $this->pdo;
        $sth = $pdo->prepare($delete->getStatement());
        $sth->execute($delete->getBindValues());
    }

}