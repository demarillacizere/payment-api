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

class PaymentsController extends A_Controller
{

    private $customersRepository;
    private $methodsRepository;
    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        $this->customersRepository = $container->get(CustomersRepository::class);
        $this->methodsRepository = $container->get(MethodsRepository::class);
        $this->routeEnum = Routes::Payments;
        $this->routeValue = Routes::Payments->value;
        $this->repository = $container->get(PaymentsRepository::class);
        
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
        $records = $this->repository->findAll();
        $responseData = [
            'type' => '',
            'title' => 'List of ' . $this->routeValue,
            'instance' => '/v1/' . $this->routeValue,
        ];
    
        if (count($records) > 0) {
            $responseData['status'] = 200;
            $responseData['detail'] = count($records);
    
            // Converting private properties to array using reflection
            $data = array_map(function ($record) {
                            $responseData[] = [
                                'id' => $record->getId(),
                                'customer_id' => $record->getCustomer()->getId(),
                                'method_id' => $record->getPaymentMethod()->getId(),
                                'amount' => $record->getAmount(),
                                'payment_date' => $record->getPaymentDate()->format('Y-m-d H:i:s'),
                            ];
                            return $responseData;
            }, $records);
    
            $responseData['data'] = $data;
            $this->logger->info('Records found:', $responseData);
            return new JsonResponse($responseData, 200);
        } else {
            $context = [
                'type' => '/errors/no_' . $this->routeValue . '_found',
                'status' => 404,
                'detail' => 'No records found',
            ];
            $this->logger->info('No ' . $this->routeValue . ' found', $context);
            return new JsonResponse($context, 404);
        }
    }

      /**
     * @OA\Post(
     *     path="/v1/payments",
     *     description="Creates a payment",
     *     @OA\RequestBody(
     *          description="Input data format",
     *          @OA\MediaType(
     *              mediaType="json",
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
     *                      property="amount",
     *                      description="Payment amount",
     *                      type="float",
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
    public function createAction(Request $request, Response $response): ResponseInterface
    {
        $requestBody = json_decode($request->getBody()->getContents(), true);
        $data = $requestBody;

        if (!$data || empty($data['customer_id']) || empty($data['method_id']) || empty($data['amount']) || empty($data['payment_date'])) {
            
                $context = [
                    'type' => '/errors/invalid_data_upon_create',
                    'title' => 'Create Payment',
                    'status' => 400,
                    'detail' => 'Invalid Data | customer_id, method_id, amount and payment_date are required',
                    'instance' => '/v1/payments'
                ];
                $this->logger->info('Invalid Data', $context);
                return new JsonResponse($context, 400);
        }
        $methodId = filter_var($data['method_id'], FILTER_SANITIZE_NUMBER_INT);
        $customerId = filter_var($data['customer_id'], FILTER_SANITIZE_NUMBER_INT);
        $amount = filter_var($data['amount'], FILTER_SANITIZE_NUMBER_FLOAT);
        $paymentDate= filter_var($data['payment_date'], FILTER_SANITIZE_SPECIAL_CHARS);

        $customer = $this->customersRepository->findById($customerId);
        $method = $this->methodsRepository->findById($methodId);

        if (!$customer) {
            $context = [
                'type' => '/errors/invalid_data_upon_create',
                'title' => 'Create Payment',
                'status' => 404,
                'detail' => 'Invalid customer_id',
                'instance' => '/v1/payments'
            ];
            $this->logger->info('Customer ID not found', $context);
            return new JsonResponse($context, 400);
        } else if (!$method) {
            $context = [
                'type' => '/errors/invalid_data_upon_create',
                'title' => 'Create Payment',
                'status' => 404,
                'detail' => 'Invalid method_id',
                'instance' => '/v1/payments'
            ];
            $this->logger->info('Method ID not found', $context);
            return new JsonResponse($context, 400);
        }
        

        $this->model = new Payments();
        $this->model->setCustomer($customer);
        $this->model->setPaymentMethod($method);
        $this->model->setAmount($amount);
        $this->model->setPaymentDate(new \DateTime($paymentDate));
        return parent::createAction($request, $response);
    }


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
     *                       property="amount",
     *                       description="Paymnet amount",
     *                       type="float",
     *                   ),
     *               ),
     *           ),
     *       ),
     * @OA\Response(
     *           response=200,
     *           description="paymnet has been updated successfully",
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
        $this->model = $payment;

        $this->model->setMethodId((int) $methodId);
        $this->model->setCustomerId((int) $customerId);
        $this->model->setAmount((float) $amount);

        return parent::updateAction($request, $response, $args);
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
