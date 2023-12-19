<?php

namespace App\Controller;

use App\Service\BookService;
use App\Service\RequestHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Annotations as OA;
use Nelmio\ApiDocBundle\Annotation\Model;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @OA\Tag(name="Books")
 * @Route("/books")
 */
class BookController extends AbstractController
{
    private BookService $bookService;
    private RequestHandler $requestHandler;

    public function __construct(BookService $bookService,
                                RequestHandler $requestHandler,)
    {
        $this->bookService = $bookService;
        $this->requestHandler = $requestHandler;
    }

    /**
     * @Route("", name="get_books", methods={"GET"})
     *
     * @OA\Get(
     *      path="/books",
     *      summary="Get list of books",
     *      tags={"Books"},
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(ref=@Model(type=Book::class))
     *          ),
     *          @OA\XmlContent(
     *              type="array",
     *              @OA\Items(ref=@Model(type=Book::class))
     *          ),
     *          content={
     *              "application/json": {},
     *              "application/xml": {}
     *          }
     *      )
     *  )
     */
    public function getBooks(Request $request): Response
    {
        try {
            $requestData = $this->requestHandler->handleRequest($request);
            $data = $this->bookService->getBooks($requestData);
            return $this->requestHandler->createResponse($data);
        } catch (\Exception $exception) {
            return $this->handleJsonError($exception);
        }
    }

    /**
     * @Route("/{id}", name="get_book", methods={"GET"})
     *
     * @OA\Get(
     *      path="/books/{id}",
     *      summary="Get a specific book by ID",
     *      tags={"Books"},
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          description="ID of the book",
     *          @OA\Schema(type="integer", format="int64")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(ref=@Model(type=Book::class)),
     *          @OA\XmlContent(ref=@Model(type=Book::class))
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Book not found"
     *      )
     *  )
     */
    public function getBook($id, Request $request): Response
    {
        try {
            $this->requestHandler->handleRequest($request);
            $data = $this->bookService->getBookById($id);
            return $this->requestHandler->createResponse($data);
        } catch (\Exception $exception) {
            return $this->handleJsonError($exception);
        }
    }

    /**
     * @Route("/createBook", name="create_book", methods={"POST"})
     * @Security("is_granted('ROLE_USER')", statusCode=401, message="Authentication failed")
     *
     * @OA\Post(
     *      path="/books/createBook",
     *      summary="Create a new book",
     *      tags={"Books"},
     *      security={{"bearerAuth": {}}},
     *      @OA\RequestBody(
     *          required=true,
     *          description="Book data for creation",
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(
     *                  type="object",
     *                  required={"title", "author", "description", "price"},
     *                  @OA\Property(property="title", type="string"),
     *                  @OA\Property(property="author", type="string"),
     *                  @OA\Property(property="description", type="string"),
     *                  @OA\Property(property="price", type="number", format="float")
     *              ),
     *              example={
     *                  "title": "test",
     *                  "author": "test",
     *                  "description": "test",
     *                  "price": 10.00
     *              }
     *          ),
     *          @OA\MediaType(
     *              mediaType="application/xml",
     *              @OA\Schema(ref="#/components/schemas/Book")
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(ref=@Model(type=Book::class)),
     *          @OA\XmlContent(ref=@Model(type=Book::class))
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Invalid data",
     *          @OA\JsonContent(
     *              @OA\Property(property="errors", type="object")
     *          )
     *      )
     *  )
     */
    public function createBook(Request $request): Response
    {
        try {
            $requestData = $this->requestHandler->handleRequest($request);

            $validationErrors = $this->validateBook($requestData);

            if (!empty($validationErrors)) {
                return $this->requestHandler->createResponse(['errors' => $validationErrors]);
            }

            $data = $this->bookService->createBook($requestData);
            return $this->requestHandler->createResponse($data);
        } catch (\Exception $exception) {
            return $this->handleJsonError($exception);
        }
    }

    /**
     * @Route("/updateBook/{id}", name="update_book", methods={"PUT"})
     * @Security("is_granted('ROLE_USER')")
     *
     * @OA\Put(
     *       path="/books/updateBook/{id}",
     *       summary="Update an existing book",
     *       tags={"Books"},
     *       security={{"bearerAuth": {}}},
     *       @OA\Parameter(
     *           name="id",
     *           in="path",
     *           required=true,
     *           description="ID of the book to update",
     *           @OA\Schema(type="integer")
     *       ),
     *       @OA\RequestBody(
     *           required=true,
     *           description="Updated book data",
     *           @OA\MediaType(
     *               mediaType="application/json",
     *               @OA\Schema(
     *                   type="object",
     *                   required={"title", "author", "description", "price"},
     *                   @OA\Property(property="title", type="string"),
     *                   @OA\Property(property="author", type="string"),
     *                   @OA\Property(property="description", type="string"),
     *                   @OA\Property(property="price", type="number", format="float")
     *               ),
     *               example={
     *                   "title": "updated test",
     *                   "author": "updated test",
     *                   "description": "updated test",
     *                   "price": 15.00
     *               }
     *           ),
     *           @OA\MediaType(
     *               mediaType="application/xml",
     *               @OA\Schema(ref="#/components/schemas/Book")
     *           )
     *       ),
     *       @OA\Response(
     *           response=200,
     *           description="Successful operation",
     *           @OA\JsonContent(ref=@Model(type=Book::class)),
     *           @OA\XmlContent(ref=@Model(type=Book::class))
     *       ),
     *       @OA\Response(
     *           response=400,
     *           description="Invalid data",
     *           @OA\JsonContent(
     *               @OA\Property(property="errors", type="object")
     *           )
     *       )
     *  )
     */
    public function updateBook($id, Request $request): Response
    {
        try {
            $requestData = $this->requestHandler->handleRequest($request);

            $validationErrors = $this->validateBook($requestData);

            if (!empty($validationErrors)) {
                return $this->requestHandler->createResponse(['errors' => $validationErrors]);
            }

            $data = $this->bookService->updateBook($id, $requestData);
            return $this->requestHandler->createResponse($data);
        } catch (\Exception $exception) {
            return $this->handleJsonError($exception);
        }
    }

    /**
     * @Route("/deleteBook/{id}", name="delete_book", methods={"DELETE"})
     * @Security("is_granted('ROLE_USER')", statusCode=401, message="Authentication failed")
     *
     * @OA\Delete(
     *       path="/books/deleteBook/{id}",
     *       summary="Delete a book by ID",
     *       tags={"Books"},
     *       security={{"bearerAuth": {}}},
     *       @OA\Parameter(
     *           name="id",
     *           in="path",
     *           required=true,
     *           description="ID of the book to delete",
     *           @OA\Schema(type="integer")
     *       ),
     *       @OA\Response(
     *           response=200,
     *           description="Successful operation",
     *           @OA\JsonContent(
     *               @OA\Property(property="message", type="string", example="Book deleted successfully")
     *           )
     *       ),
     *       @OA\Response(
     *           response=404,
     *           description="Book not found",
     *           @OA\JsonContent(
     *               @OA\Property(property="message", type="string", example="Book not found")
     *           )
     *       )
     *  )
     */
    public function deleteBook($id, Request $request): Response
    {
        try {
            $this->requestHandler->handleRequest($request);
            $data = $this->bookService->deleteBook($id);
            return $this->requestHandler->createResponse($data);
        } catch (\Exception $exception) {
            return $this->handleJsonError($exception);
        }
    }

    private function handleJsonError(\Exception $exception): Response
    {
        return $this->requestHandler->createResponse(['message' => $exception->getMessage()]);
    }

    function validateBook(array $data): array
    {
        $validator = Validation::createValidator();

        $constraints = new Assert\Collection([
            'title' => new Assert\NotBlank(['message' => 'Title cannot be blank']),
            'author' => new Assert\NotBlank(['message' => 'Author cannot be blank']),
            'description' => [
                new Assert\NotBlank(['message' => 'Description cannot be blank']),
                new Assert\Length([
                    'min' => 10,
                    'minMessage' => 'Description should be at least {{ limit }} characters long'
                ]),
            ],
            'price' => [
                new Assert\NotBlank(['message' => 'Price cannot be blank']),
                new Assert\Type(['type' => 'float', 'message' => 'Price must be a valid number']),
            ],
        ]);

        $violations = $validator->validate($data, $constraints);

        $errors = [];
        foreach ($violations as $violation) {
            $errors[$violation->getPropertyPath()][] = $violation->getMessage();
        }

        return $errors;
    }
}