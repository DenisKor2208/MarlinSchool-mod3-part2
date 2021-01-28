<?php

namespace App\controllers;

use App\exceptions\AccountIsBlockedException;
use App\exceptions\NotEnoughMoneyException;
use App\QueryBuilder;
use Aura\SqlQuery\QueryFactory;
use Delight\Auth\Auth;
use Exception;
use JasonGrimes\Paginator;
use League\Plates\Engine;
use Faker\Factory;
use PDO;
use SimpleMail;
use Tamtamchik\SimpleFlash\Flash;

class PageController
{
    private $templates,
            $qb,
            $pdo,
            $queryFactory,
            $auth;

    public function __construct(Engine $engine, QueryBuilder $qb, PDO $pdo, QueryFactory $queryFactory, Auth $auth)
    {
        $this->templates = $engine;
        $this->qb = $qb;
        $this->pdo = $pdo;
        $this->queryFactory = $queryFactory;
        $this->auth = $auth;

    }

    public function homepage($vars)
    {
        $quantityRecords = 10;

        $totalItems = $this->qb->getAll('posts'); //получаем общее кол-во записей

        $select = $this->queryFactory->newSelect();
        $select->cols(['*']) -> from('posts')
            -> setPaging($quantityRecords) //по столько-то постов на странице
            -> page($vars['id'] ?? 1); //какая страница сейчас открыта
        $sth = $this->pdo->prepare($select->getStatement());
        $sth->execute($select->getBindValues());
        $items = $sth->fetchAll(PDO::FETCH_ASSOC);

        $itemsPerPage = $quantityRecords; //сколько постов будет на странице
        $currentPage = $vars['id'] ?? 1; //какая страница сейчас открыта
        $urlPattern = '/homepage/(:num)';

        $paginator = new Paginator(count($totalItems), $itemsPerPage, $currentPage, $urlPattern);

        echo $this->templates->render('homepage',
            [
             'postsInView' => $items,
             'paginator' => $paginator
            ]);
    }

    public function about()
    {
        /************************************************************/
        $total = 10;
        $amount = 1100;
        $accountInBlocked = "unblocked";

        try {
            /*
             * В месте try{} может быть как прямой код так и функция с  throw new Exception('Send error') внутри;
             */
            if ($accountInBlocked === "Blocked") {
                throw new AccountIsBlockedException('Ваш аккаунт заблокирован');
            } elseif ($amount > $total) {
                throw new NotEnoughMoneyException('Недостаточно средств: ' . $total . ". Запрашиваете: " . $amount);
            }
        } catch (AccountIsBlockedException $a) {
            flash()->warning($a->getMessage());
        } catch (NotEnoughMoneyException $n) {
            flash()->error($n->getMessage());
        }
        /************************************************************/

        echo $this->templates->render('about');
    }

    public function register()
    {
        $email = 'denis@denis.com';
        $password = '12345';
        $username = 'DenisKV';
        $sendOne = '';

        try {
            $userId = $this->auth->register($email, $password, $username, function ($selector, $token) {
                $sendOne = 'Send ' . $selector . ' and ' . $token . ' to the user (e.g. via email)';
                flash()->success(["We have signed up a new user.", $sendOne]);
            });

        }
        catch (\Delight\Auth\InvalidEmailException $e) {
            flash()->error('Invalid email address'); die();
        }
        catch (\Delight\Auth\InvalidPasswordException $e) {
            flash()->error('Invalid password'); die();
        }
        catch (\Delight\Auth\UserAlreadyExistsException $e) {
            flash()->error('User already exists'); die();
        }
        catch (\Delight\Auth\TooManyRequestsException $e) {
            flash()->error('Too many requests'); die();
        }

        echo $this->templates->render('register');
    }

    public function email_verification($vars)
    {
        try {
            $this->auth->confirmEmail($vars['selector'], $vars['token']);

            flash()->success('Email address has been verified');
        }
        catch (\Delight\Auth\InvalidSelectorTokenPairException $e) {
            flash()->error('Invalid token'); die();
        }
        catch (\Delight\Auth\TokenExpiredException $e) {
            flash()->error('Token expired'); die();
        }
        catch (\Delight\Auth\UserAlreadyExistsException $e) {
            flash()->error('Email address already exists'); die();
        }
        catch (\Delight\Auth\TooManyRequestsException $e) {
            flash()->error('Too many requests'); die();
        }

        echo $this->templates->render('register');
    }

    public function login($vars) {

        try {
            $this->auth->login($vars['email'], $vars['password']);
            flash()->success('User is logged in');
        }
        catch (\Delight\Auth\InvalidEmailException $e) {
            flash()->error('Wrong email address'); die();
        }
        catch (\Delight\Auth\InvalidPasswordException $e) {
            flash()->error('Wrong password'); die();
        }
        catch (\Delight\Auth\EmailNotVerifiedException $e) {
            flash()->error('Email not verified'); die();
        }
        catch (\Delight\Auth\TooManyRequestsException $e) {
            flash()->error('Too many requests'); die();
        }

        echo $this->templates->render('register', ['auth' => $this->auth]);
    }

    public function userView()
    {
        echo $this->templates->render('register', ['auth' => $this->auth]);
    }

    public function sendEmail()
    {
        $mailer = new SimpleMail();
        SimpleMail::make()
            ->setTo('chtil@list.ru', 'Алена')
            ->setFrom('dkorotin@list.ru', 'Admin')
            ->setSubject('Тестовая тема письма')
            ->setMessage('Здравствуйте! Это тестовое письмо!')
            ->send();

        echo "Почта отправлена!";
    }

}