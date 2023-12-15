<?php

namespace App\Controller;

use App\Service\BookService;
use App\Service\RequestHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class BookController extends AbstractController
{
    private $bookService;
    private $requestHandler;

    public function __construct(BookService $bookService, RequestHandler $requestHandler)
    {
        $this->bookService = $bookService;
        $this->requestHandler = $requestHandler;
    }

    /**
     * @Route("/books", name="get_books", methods={"GET"})
     */
    public function getBooks(Request $request): Response
    {
        $request = $this->requestHandler->handleRequest($request);
        $data = $this->bookService->getBooks($request);

        return $this->requestHandler->createResponse($data);
    }
  
    /**
     * @Route("/books/{id}", name="get_book", methods={"GET"})
     */
    public function getBook($id, Request $request): Response
    {
        $this->requestHandler->handleRequest($request);
        $data = $this->bookService->getBooksById($id);

        return $this->requestHandler->createResponse($data);
    }

    /**
     * @Route("/books", name="create_book", methods={"POST"})
     * @IsGranted("ROLE_USER")
     */
    public function createBook(Request $request): Response
    {
        try {
            $validData = $this->requestHandler->handleRequest($request);
            $data = $this->bookService->createBook($validData);

            return $this->requestHandler->createResponse((array)$data);
        } catch (AccessDeniedException $e) {
            $data = [
                'error' => 'Unauthorized',
                'message' => 'Full authentication is required to access this resource.',
            ];
            return $this->requestHandler->createResponse($data);
        }
    }

    /**
     * @Route("/books/{id}", name="update_book", methods={"PUT"})
     * @IsGranted("ROLE_USER")
     */
    public function updateBook($id, Request $request): Response
    {
        $validData = $this->requestHandler->handleRequest($request);
        $data = $this->bookService->updateBook($id, $validData);

        return $this->requestHandler->createResponse($data);
    }

    /**
     * @Route("/books/{id}", name="delete_book", methods={"DELETE"})
     * @IsGranted("ROLE_USER")
     */
    public function deleteBook($id, Request $request): Response
    {
        $validData = $this->requestHandler->handleRequest($request);
        $data = $this->bookService->deleteBook($id, $validData);

        return $this->requestHandler->createResponse($data);
    }
}