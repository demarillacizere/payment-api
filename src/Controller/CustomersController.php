<?php

namespace PaymentApi\Controller;

use Laminas\Diactoros\Response\JsonResponse;
use PaymentApi\Model\Customers;
use PaymentApi\Repository\CustomersRepository;
use PaymentApi\Routes;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * @OA\Info(
 *     title="Payment API",
 *     version="1.0",
 *     description="API for managing Customers and Payments.",
 * )
 */

/**
 * @OA\Server(
 *     url="http://localhost:8000",
 *     description="Payment API Server"
 * )
 */

/**
 * CustomersController
 */
final class CustomersController extends A_Controller
{
    /**
     * Method __construct
     *
     * @param ContainerInterface $container [explicite description]
     *
     * @return void
     */
    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        $this->routeEnum = Routes::Customers;
        $this->routeValue = Routes::Customers->value;
        $this->repository = $container->get(CustomersRepository::class);
    }

    /**
     * @OA\Get(
     *     path="/v1/customers",
     *     description="Returns all customers",
     *     @OA\Response(
     *          response=200,
     *          description="customers response",
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="Internal server error",
     *      ),
     *   )
     * )
     * @return \Laminas\Diactoros\Response
     */
    public function indexAction(Request $request, Response $response): ResponseInterface
    {
        return parent::indexAction($request, $response);
    }


    /**
     * @OA\Post(
     *     path="/v1/customers",
     *     description="Creates a customer",
     *     @OA\RequestBody(
     *          description="Input data format",
     *          @OA\MediaType(
     *              mediaType="json",
     *              @OA\Schema(
     *                  type="object",
     *                  @OA\Property(
     *                      property="name",
     *                      description="name of new customer",
     *                      type="string",
     *                  ),
     *                   @OA\Property(
     *                      property="address",
     *                      description="address of new customers",
     *                      type="string",
     *                  ),
     *              ),
     *          ),
     *      ),
     *     @OA\Response(
     *          response=200,
     *          description="Customer has been created successfully",
     *      ),
     *     @OA\Response(
     *          response=400,
     *          description="bad request",
     *      ),
     *      @OA\Response(
     *            response=500,
     *            description="Internal server error",
     *        ),
     *   ),
     * )
     * @param \Slim\Psr7\Request $request
     * @param \Slim\Psr7\Response $response
     * @return ResponseInterface
     */
    public function createAction(Request $request, Response $response): ResponseInterface
    {
        try {
            $requestBody = json_decode($request->getBody()->getContents(), true);
    
            // Validate the required fields
            if (!isset($requestBody['name']) || !isset($requestBody['address'])) {
                $context = [
                    'type' => '/errors/missing_fields',
                    'title' => 'Missing required fields',
                    'status' => 400,
                    'detail' => 'The "name" and "address" fields are required.'
                ];
                $this->logger->info('Missing required fields', $context);
                return new JsonResponse($context, 400);
            }
    
            $name = filter_var($requestBody['name'], FILTER_SANITIZE_SPECIAL_CHARS);
            $address = filter_var($requestBody['address'], FILTER_SANITIZE_SPECIAL_CHARS);
    
            $this->model = new Customers();
            $this->model->setName($name);
            $this->model->setAddress($address);
            $this->model->setIsActive(true);
    
            // Call the parent createAction method
            return parent::createAction($request, $response);
        } catch (\Exception $e) {
            // Handle other exceptions (500 Internal Server Error)
            $context = [
                'type' => '/errors/internal_server_error',
                'title' => 'Internal Server Error',
                'status' => 500,
                'detail' => $e->getMessage()
            ];
            $this->logger->error('Internal Server Error', $context);
            return new JsonResponse($context, 500);
        }
    }
    

    /**
     * @OA\Delete(
     *     path="/v1/customers/{id}",
     *     description="deletes a single customer based on customer's ID",
     *     @OA\Parameter(
     *         description="ID of customer to delete",
     *         in="path",
     *         name="id",
     *         required=true,
     *         @OA\Schema(
     *             format="int64",
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="customer has been deleted"
     *     ),
     * @OA\Response(
     *            response=400,
     *            description="bad request",
     *        ),
     * @OA\Response(
     *                 response=404,
     *             description="Customer not found",
     *         ),
     * @OA\Response(
     *             response=500,
     *             description="Internal server error",
     *         ),
     *   )
     */
    public function removeAction(Request $request, Response $response, array $args): ResponseInterface
    {
        return parent::removeAction($request, $response, $args);
    }

    /**
     * @OA\Put(
     *     path="/v1/customers/{id}",
     *     description="update a single customer based on customer's ID",
     *     @OA\Parameter(
     *          description="ID of a customer to update",
     *          in="path",
     *          name="id",
     *          required=true,
     *          @OA\Schema(
     *              format="int64",
     *              type="integer"
     *          )
     *      ),
     *     @OA\RequestBody(
     *           description="Input data format",
     *           @OA\MediaType(
     *               mediaType="json",
     *               @OA\Schema(
     *                   type="object",
     *                   @OA\Property(
     *                       property="name",
     *                       description="name of customer",
     *                       type="string",
     *                   ),
     *                  @OA\Property(
     *                       property="address",
     *                       description="address of customer",
     *                       type="string",
     *                   ),
     *               ),
     *           ),
     *       ),
     * @OA\Response(
     *           response=200,
     *           description="customer has been created successfully",
     *       ),
     * @OA\Response(
     *           response=400,
     *           description="bad request",
     *       ),
     *     @OA\Response(
     *                response=404,
     *            description="customer not found",
     *        ),
     *     @OA\Response(
     *            response=500,
     *            description="Internal server error",
     *        ),
     *  )
     * @param \Slim\Psr7\Request $request
     * @param \Slim\Psr7\Response $response
     * @param array $args
     * @return ResponseInterface
     */
    public function updateAction(Request $request, Response $response, array $args): ResponseInterface
{
    try {
        $requestBody = json_decode($request->getBody()->getContents(), true);
        if (!isset($requestBody['name']) || !isset($requestBody['address'])) {
            $context = [
                'type' => '/errors/missing_fields',
                'title' => 'Missing required fields',
                'status' => 400,
                'detail' => 'The "name" and "address" fields are required.'
            ];
            $this->logger->info('Missing required fields', $context);
            return new JsonResponse($context, 400);
        }

        $name = filter_var($requestBody['name'], FILTER_SANITIZE_SPECIAL_CHARS);
        $address = filter_var($requestBody['address'], FILTER_SANITIZE_SPECIAL_CHARS);

        /** @var Customers $customer */
        $customer = $this->repository->findById($args['id']);
        if (is_null($customer)) {
            $context = [
                'type' => '/errors/no_customers_found_upon_update',
                'title' => 'List of customers',
                'status' => 404,
                'detail' => $args['id'],
                'instance' => '/v1/customers/{id}'
            ];
            $this->logger->info('No customers found', $context);
            return new JsonResponse($context, 404);
        }

        $this->model = $customer;
        $this->model->setName($name);
        $this->model->setAddress($address);
        return parent::updateAction($request, $response, $args);
    } catch (\Exception $e) {
        $context = [
            'type' => '/errors/internal_server_error',
            'title' => 'Internal Server Error',
            'status' => 500,
            'detail' => $e->getMessage()
        ];
        $this->logger->error('Internal Server Error', $context);
        return new JsonResponse($context, 500);
    }
}


    /**
     * @OA\Get(
     *     path="/v1/customers/deactivate/{id}",
     *     description="Deactivates a single customer based on customer's ID",
     *     @OA\Parameter(
     *          description="ID of a customer to update",
     *          in="path",
     *          name="id",
     *          required=true,
     *          @OA\Schema(
     *              format="int64",
     *              type="integer"
     *          )
     *      ),
     * @OA\Response(
     *           response=200,
     *           description="Customer has been deactivated successfully",
     *       ),
     * @OA\Response(
     *           response=400,
     *           description="bad request",
     *       ),
     *     @OA\Response(
     *                response=404,
     *            description="Customer not found",
     *        ),
     *     @OA\Response(
     *            response=500,
     *            description="Internal server error",
     *        ),
     *  )
     * @param \Slim\Psr7\Request $request
     * @param \Slim\Psr7\Response $response
     * @param array $args
     * @return ResponseInterface
     */
    public function deactivateAction(Request $request, Response $response, array $args): ResponseInterface
    {
        return parent::deactivateAction($request, $response, $args);
    }

    /**
     * @OA\Get(
     *     path="/v1/customers/reactivate/{id}",
     *     description="Reactivates a single customer based on customer's ID",
     *     @OA\Parameter(
     *          description="ID of a customer to update",
     *          in="path",
     *          name="id",
     *          required=true,
     *          @OA\Schema(
     *              format="int64",
     *              type="integer"
     *          )
     *      ),
     * @OA\Response(
     *           response=200,
     *           description="Customer has been reactivated successfully",
     *       ),
     * @OA\Response(
     *           response=400,
     *           description="bad request",
     *       ),
     *     @OA\Response(
     *                response=404,
     *            description="Customer not found",
     *        ),
     *     @OA\Response(
     *            response=500,
     *            description="Internal server error",
     *        ),
     *  )
     * @param \Slim\Psr7\Request $request
     * @param \Slim\Psr7\Response $response
     * @param array $args
     * @return ResponseInterface
     */
    public function reactivateAction(Request $request, Response $response, array $args): ResponseInterface
    {
        return parent::reactivateAction($request, $response, $args);
    }
}