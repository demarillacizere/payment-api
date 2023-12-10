<?php
namespace App;

use DI\Container;
use Doctrine\ORM\EntityManager;
use Mockery;
use Monolog\Logger;
use PaymentApi\Controller\MethodsController;
use PaymentApi\Repository\MethodsRepository;
use PaymentApi\Repository\MethodsRepositoryDoctrine;
use PHPUnit\Framework\TestCase;


class MethodControllerTest extends TestCase
{
    private Container $container;

    protected function setUp(): void
    {
        $container = new Container();
        $container->set(EntityManager::class, function(Container $c) {
            return Mockery::mock('Doctrine\ORM\EntityManager');
        });

        $container->set(MethodsRepository::class, function(Container $c) {
            $em = $c->get(EntityManager::class);
            return new MethodsRepositoryDoctrine($em);
        });

        $container->set(Logger::class, function(Container $c) {
            return Mockery::mock('Monolog\Logger');
        });

        $this->container = $container;
    }
    public function testCreateInstanceOfMethodsController()
    {
        $abstractControllerObject = new MethodsController($this->container);
        $this->assertInstanceOf('PaymentApi\Controller\MethodsController', $abstractControllerObject);
    }
}
