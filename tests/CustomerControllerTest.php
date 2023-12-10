<?php
namespace App;

use DI\Container;
use Doctrine\ORM\EntityManager;
use Mockery;
use Monolog\Logger;
use PaymentApi\Controller\CustomersController;
use PaymentApi\Repository\CustomersRepository;
use PaymentApi\Repository\CustomersRepositoryDoctrine;
use PHPUnit\Framework\TestCase;


class CustomerControllerTest extends TestCase
{
    private Container $container;

    protected function setUp(): void
    {
        $container = new Container();
        $container->set(EntityManager::class, function(Container $c) {
            return Mockery::mock('Doctrine\ORM\EntityManager');
        });

        $container->set(CustomersRepository::class, function(Container $c) {
            $em = $c->get(EntityManager::class);
            return new CustomersRepositoryDoctrine($em);
        });

        $container->set(Logger::class, function(Container $c) {
            return Mockery::mock('Monolog\Logger');
        });

        $this->container = $container;
    }
    public function testCreateInstanceOfCustomersController()
    {
        $abstractControllerObject = new CustomersController($this->container);
        $this->assertInstanceOf('PaymentApi\Controller\CustomersController', $abstractControllerObject);
    }
}
