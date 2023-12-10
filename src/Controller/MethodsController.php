<?php
/**
 * MethodsController.php
 * demarillac.izere
 * 26.09.2023
 */

namespace PaymentApi\Controller;

use Laminas\Diactoros\Response\JsonResponse;
use PaymentApi\Model\Methods;
use PaymentApi\Repository\MethodsRepository;
use PaymentApi\Routes;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class MethodsController extends A_Controller
{
    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        $this->routeEnum = Routes::Methods;
        $this->routeValue = Routes::Methods->value;
        $this->repository = $container->get(MethodsRepository::class);
    }

    /**
     * @OA\Get(
     *     path="/v1/methods",
     *     description="Returns all payment methods",
     *     @OA\Response(
     *          response=200,
     *          description="methods response",
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="Internal server error",
     *      ),
     *   )
     * )
     * @return \Laminas\Diactoros\Response
     */
    public function indexAction(Request $request, ResponseInterface $response): ResponseInterface
    {
        $methods = $this->repository->findAll();
        $methodsDetails = [];
        foreach ($methods as $method) {
            $methodDetails[] = [
                'id' => $method->getId(),
                'name' => $method->getName(),
                'IsActive' => $method->isActive(), 
            ];
        }

        // Return the array of customer details as JSON response
        return new JsonResponse($methodDetails);
    }

    /**
     * @OA\Post(
     *     path="/v1/methods",
     *     description="Creates a payment method",
     *     @OA\RequestBody(
     *          description="Input data format",
     *          @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Schema(
     *                  type="object",
     *                  @OA\Property(
     *                      property="name",
     *                      description="name of new payment method",
     *                      type="string",
     *                  ),
     *              ),
     *          ),
     *      ),
     *     @OA\Response(
     *          response=200,
     *          description="payment method has been created successfully",
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
        $name = filter_var($requestBody['name'], FILTER_SANITIZE_SPECIAL_CHARS);

        $this->model = new Methods();
        $this->model->setName($name);
        $this->model->setIsActive(true);

        return parent::createAction($request, $response);
    }


    /**
     * @OA\Put(
     *     path="/v1/methods/{id}",
     *     description="update a single paymnet method based on method ID",
     *     @OA\Parameter(
     *          description="ID of a payment method to update",
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
     *               mediaType="multipart/form-data",
     *               @OA\Schema(
     *                   type="object",
     *                   @OA\Property(
     *                       property="name",
     *                       description="name of paymnet method",
     *                       type="string",
     *                   ),
     *               ),
     *           ),
     *       ),
     * @OA\Response(
     *           response=200,
     *           description="paymnet method has been created successfully",
     *       ),
     * @OA\Response(
     *           response=400,
     *           description="bad request",
     *       ),
     *     @OA\Response(
     *                response=404,
     *            description="Method not found",
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
        $name = filter_var($requestBody['name'], FILTER_SANITIZE_SPECIAL_CHARS);

        $method = $this->repository->findById($args['id']);
        if (is_null($method)) {
            $context = [
                'type' => '/errors/no_methods_found_upon_update',
                'title' => 'List of methods',
                'status' => 404,
                'detail' => $args['id'],
                'instance' => '/v1/methods/{id}'
            ];
            $this->logger->info('No methods found', $context);
            return new JsonResponse($context, 404);
        }
        $this->model = $method;
        $method->setName($name);

        return parent::updateAction($request, $response, $args);
    }

    /**
     * @OA\Get(
     *     path="/v1/methods/deactivate/{id}",
     *     description="Deactivates a single paymnet method based on method ID",
     *     @OA\Parameter(
     *          description="ID of a payment method to update",
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
     *           description="paymnet method has been deactivated successfully",
     *       ),
     * @OA\Response(
     *           response=400,
     *           description="bad request",
     *       ),
     *     @OA\Response(
     *                response=404,
     *            description="Method not found",
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
     *     path="/v1/methods/reactivate/{id}",
     *     description="Reactivates a single paymnet method based on method ID",
     *     @OA\Parameter(
     *          description="ID of a payment method to update",
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
     *           description="paymnet method has been reactivated successfully",
     *       ),
     * @OA\Response(
     *           response=400,
     *           description="bad request",
     *       ),
     *     @OA\Response(
     *                response=404,
     *            description="Method not found",
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
     *     path="/v1/methods/{id}",
     *     description="deletes a single paymnet method based on method ID",
     *     @OA\Parameter(
     *         description="ID of method to delete",
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
     *         description="paymnet method has been deleted"
     *     ),
     * @OA\Response(
     *            response=400,
     *            description="bad request",
     *        ),
     * @OA\Response(
     *                 response=404,
     *             description="Method not found",
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
