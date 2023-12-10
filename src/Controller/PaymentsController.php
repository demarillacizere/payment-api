<?php


namespace PaymentApi\Controller;

use Laminas\Diactoros\Response\JsonResponse;
use PaymentApi\Model\Payments;
use PaymentApi\Repository\PaymentsRepository;
use PaymentApi\Routes;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use PaymentApi\Repository\CustomersRepository;
use PaymentApi\Repository\MethodsRepository;
use DateTime;
use Slim\Psr7\Message;

class PaymentsController extends A_Controller
{
    private $paymentsRepository;
    private $customersRepository;
    private $methodsRepository;
    public function __construct(ContainerInterface $container, PaymentsRepository $paymentsRepository, CustomersRepository $customerRepository, MethodsRepository $methodsRepository)
    {
        parent::__construct($container);
        $this->paymentsRepository = $paymentsRepository;
        $this->customersRepository = $customerRepository;
        $this->methodsRepository = $methodsRepository;
    }

    /**
     * @OA\Get(
     *     path="/v1/payments",
     *     description="Returns all payments",
     *     @OA\Response(
     *          response=200,
     *          description="paymnets response",
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
        $payments = $this->paymentsRepository->findAll();

        if (empty($payments)) {
            $this->logger->info('No payment transaction found.', ['status_code' => 404]);
            $data = ['message' => 'No payment transaction found'];
            $statusCode = 404;
        } else {
            $responseData = [];
            foreach ($payments as $payment) {
                $responseData[] = [
                    'id' => $payment->getId(),
                    'customer_id' => $payment->getCustomer()->getId(),
                    'method_id' => $payment->getPaymentMethod()->getId(),
                    'amount' => $payment->getAmount(),
                    'payment_date' => $payment->getPaymentDate()->format('Y-m-d H:i:s'),
                ];
            }

            $this->logger->info('Payments transaction list retrieved.', ['status_code' => 200]);
            $data = $responseData;
            $statusCode = 200;
        }

        $response->getBody()->write(json_encode($data));

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($statusCode);
    }
    public function createAction(Request $request, Response $response): ResponseInterface
    {
        $parsedBody = $request->getParsedBody();
        $contentType = $request->getHeaderLine('Content-Type');

        if (empty($parsedBody)) {
            $jsonBody = json_decode($request->getBody()->getContents(), true);
            if (!empty($jsonBody)) {
                $data = $jsonBody;
            } else {
                $context = [
                    'type' => '/errors/invalid_data_upon_create',
                    'title' => 'Create Payment',
                    'status' => 400,
                    'detail' => 'Invalid Variables',
                    'instance' => '/v1/payments'
                ];
                $this->logger->info('Invalid Data', $context);
                return new JsonResponse($context, 400);
            }
        } else {
            $data = $parsedBody;
        }

        if (!$data || empty($data['customer_id']) || empty($data['method_id']) || empty($data['amount']) || empty($data['payment_date'])) {
            
                $context = [
                    'type' => '/errors/invalid_data_upon_create',
                    'title' => 'Create Payment',
                    'status' => 400,
                    'detail' => 'Invalid Credentials',
                    'instance' => '/v1/payments'
                ];
                $this->logger->info('Invalid Data', $context);
                return new JsonResponse($context, 400);
            // $this->logger->info('Invalid Data.', ['statusCode' => 400]);
            // $response->getBody()->write(json_encode(['message' => 'Invalid data']));
            // return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        $customer = $this->customersRepository->findById($data['customer_id']);
        $method = $this->methodsRepository->findById($data['method_id']);

        if (!$customer) {
            $this->logger->info('Invalid Customer ID.', ['statusCode' => 400]);
            $response->getBody()->write(json_encode(['message' => 'Invalid customer ID']));
            return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
        } else if (!$method) {
            $this->logger->info('Invalid payment method ID.', ['statusCode' => 400]);
            $response->getBody()->write(json_encode(['message' => 'Invalid payment method ID']));
            return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
        }

        $payment = new Payments();
        $payment->setCustomer($customer);
        $payment->setPaymentMethod($method);
        $payment->setAmount($data['amount']);
        $payment->setPaymentDate(new \DateTime($data['payment_date']));

        try {
            $this->paymentsRepository->store($payment);

            $this->logger->info('Payment transaction created.', ['payment_id' => $payment->getId()]);

            $response->getBody()->write(json_encode(['message' => 'Payment transaction created successfully']));
            return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {
                $context = [
                    'type' => '/errors/no_customers_found_upon_update',
                    'title' => 'List of customers',
                    'status' => 404,
                    'detail' => $e,
                    'instance' => '/v1/customers/{id}'
                ];
                $this->logger->info('No customers found', $context);
                return new JsonResponse($context, 500);
            
        }
    }


    /**
     * @OA\Post(
     *     path="/v1/payments",
     *     description="Creates a payment",
     *     @OA\RequestBody(
     *          description="Input data format",
     *          @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Schema(
     *                  type="object",
     *                  @OA\Property(
     *                      property="method_id",
     *                      description="ID of paymnet method",
     *                      type="integer",
     *                  ),
     *              ),
     *              @OA\Schema(
     *                  type="object",
     *                  @OA\Property(
     *                      property="customer_id",
     *                      description="ID of customer",
     *                      type="integer",
     *                  ),
     *              ),
     *              @OA\Schema(
     *                  type="object",
     *                  @OA\Property(
     *                      property="order_id",
     *                      description="ID of order",
     *                      type="integer",
     *                  ),
     *              ),
     *              @OA\Schema(
     *                  type="object",
     *                  @OA\Property(
     *                      property="sum",
     *                      description="Paymnet amount",
     *                      type="float",
     *                  ),
     *              ),
     *              @OA\Schema(
     *                  type="object",
     *                  @OA\Property(
     *                      property="is_finalized",
     *                      description="If paymnet is finalized",
     *                      type="integer",
     *                  ),
     *              ),
     *          ),
     *      ),
     *     @OA\Response(
     *          response=200,
     *          description="payment has been created successfully",
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


    /**
     * @OA\Put(
     *     path="/v1/payments/{id}",
     *     description="update a single paymnet based on payment ID",
     *     @OA\Parameter(
     *          description="ID of a payment to update",
     *          in="path",
     *          name="id",
     *          required=true,
     *          @OA\Schema(
     *              format="int64",
     *              type="integer"
     *          )
     *      ),
    
     *           description="Input data format",
     *           @OA\MediaType(
     *               mediaType="multipart/form-data",
     *               @OA\Schema(
     *                   type="object",
     *                   @OA\Property(
     *                       property="method_id",
     *                       description="ID of paymnet method",
     *                       type="integer",
     *                   ),
     *               ),
     *               @OA\Schema(
     *                   type="object",
     *                   @OA\Property(
     *                       property="customer_id",
     *                       description="ID of customer",
     *                       type="integer",
     *                   ),
     *               ),
     *               @OA\Schema(
     *                   type="object",
     *                   @OA\Property(
     *                       property="order_id",
     *                       description="ID of order",
     *                       type="integer",
     *                   ),
     *               ),
     *               @OA\Schema(
     *                   type="object",
     *                   @OA\Property(
     *                       property="",
     *                       description="Paymnet amount",
     *                       type="float",
     *                   ),
     *               ),
     *               @OA\Schema(
     *                   type="object",
     *                   @OA\Property(
     *                       property="is_finalized",
     *                       description="If paymnet is finalized",
     *                       type="integer",
     *                   ),
     *               ),
     *           ),
     *       ),
     * @OA\Response(
     *           response=200,
     *           description="paymnet has been created successfully",
     *       ),
     * @OA\Response(
     *           response=400,
     *           description="bad request",
     *       ),
     *     @OA\Response(
     *                response=404,
     *            description="Paymnet not found",
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
        $requestBody = json_decode($request->getBody()->getContents(), true);
        $methodId = filter_var($requestBody['method_id'], FILTER_SANITIZE_NUMBER_INT);
        $customerId = filter_var($requestBody['customer_id'], FILTER_SANITIZE_NUMBER_INT);
        $amount = filter_var($requestBody['amount'], FILTER_SANITIZE_NUMBER_FLOAT);

        $payment = $this->repository->findById($args['id']);
        if (is_null($payment)) {
            $context = [
                'type' => '/errors/no_payment_found_upon_update',
                'title' => 'List of methods',
                'status' => 404,
                'detail' => $args['id'],
                'instance' => '/v1/payments/{id}'
            ];
            $this->logger->info('No payments found', $context);
            return new JsonResponse($context, 404);
        }


        //@TODO: check the relations OneToMany in Doctrine
        $this->model = $payment;

        $this->model->setMethodId((int) $methodId);
        $this->model->setCustomerId((int) $customerId);
        $this->model->setAmount((float) $amount);

        return parent::updateAction($request, $response, $args);
    }

    /**
     * @OA\Get(
     *     path="/v1/paymnets/deactivate/{id}",
     *     description="Deactivates a single paymnet based on payment ID",
     *     @OA\Parameter(
     *          description="ID of a payment to update",
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
     *           description="paymnet has been deactivated successfully",
     *       ),
     * @OA\Response(
     *           response=400,
     *           description="bad request",
     *       ),
     *     @OA\Response(
     *                response=404,
     *            description="Payment not found",
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
     *     path="/v1/payment/reactivate/{id}",
     *     description="Reactivates a single paymnet based on payment ID",
     *     @OA\Parameter(
     *          description="ID of a payment to update",
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
     *           description="paymnet has been reactivated successfully",
     *       ),
     * @OA\Response(
     *           response=400,
     *           description="bad request",
     *       ),
     *     @OA\Response(
     *                response=404,
     *            description="Payment not found",
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

    /**
     * @OA\Delete(
     *     path="/v1/payment/{id}",
     *     description="deletes a single paymnet based on payment ID",
     *     @OA\Parameter(
     *         description="ID of payment to delete",
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
     *         description="paymnet has been deleted"
     *     ),
     * @OA\Response(
     *            response=400,
     *            description="bad request",
     *        ),
     * @OA\Response(
     *                 response=404,
     *             description="Payment not found",
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
}
