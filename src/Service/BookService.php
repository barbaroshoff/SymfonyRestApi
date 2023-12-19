<?php

namespace App\Service;

use App\Entity\Book;
use App\Repository\BookRepository;
use App\Validator\BookValidator;
use App\Validator\Constraints\BookConstraint;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraint;

class BookService
{
    private $bookRepository;

    public function __construct(BookRepository $bookRepository)
    {
        $this->bookRepository = $bookRepository;
    }
    public function getBooks($requestData): array
    {
        $limit = $requestData['limit'] ?? 10;
        $offset = $requestData['offset'] ?? 0;
        $author = $requestData['authorSearch'] ?? null;

        if ($author !== null) {
            // Получение книг по автору, если указан параметр author
            $books = $this->bookRepository->getBooksByAuthor($author, $limit, $offset);
        } else {
            // Если параметр author не указан, получение книг с учетом limit и offset
            $books = $this->bookRepository->getBooksByLimitOffset($limit, $offset);
        }
        $booksArray = [];
        foreach ($books as $book) {
            $bookData = [
                'id' => $book->getId(),
                'title' => $book->getTitle(),
                'author' => $book->getAuthor(),
                'description' => $book->getDescription(),
                'price' => $book->getPrice()
            ];

            $booksArray[] = $bookData;
        }

        return $booksArray;
    }

    public function getBookById($id): array
    {
        $book = $this->bookRepository->find($id);

        if (!$book instanceof Book) {
            return [];
        }

        return [
            'id' => $book->getId(),
            'title' => $book->getTitle(),
            'author' => $book->getAuthor(),
            'description' => $book->getDescription(),
            'price' => $book->getPrice()
        ];
    }

    public function createBook($data): array
    {
        $book = new Book();
        $book->setTitle($data['title'] ?? null);
        $book->setAuthor($data['author'] ?? null);
        $book->setDescription($data['description'] ?? null);
        $book->setPrice($data['price'] ?? null);

        try {
            $book = $this->bookRepository->createBook($book);

            if ($book instanceof Book) {
                return [
                    'message' => 'Book created successfully'
                ];
            }
        } catch (\Exception $e) {

        }

        return [
            'message' => 'Book created unsuccessfully'
        ];
    }

    public function updateBook(int $bookId, array $updatedData): array
    {
        try {
            $book = $this->bookRepository->find($bookId);
            if (!$book) {
                return [
                    'message' => 'Book not found'
                ];
            }

            if (isset($updatedData['title'])) {
                $book->setTitle($updatedData['title']);
            }

            if (isset($updatedData['author'])) {
                $book->setAuthor($updatedData['author']);
            }

            if (isset($updatedData['description'])) {
                $book->setDescription($updatedData['description']);
            }

            if (isset($updatedData['price'])) {
                $book->setPrice($updatedData['price']);
            }

            $updatedBook = $this->bookRepository->saveBook($book);
            if ($updatedBook instanceof Book) {
                return [
                    'message' => 'Book updated successfully'
                ];
            }
        } catch (\Exception $exception) {
        }

        return [
            'message' => 'Book updated unsuccessfully'
        ];
    }

    public function deleteBook($id): array
    {
        $isDeleted = $this->bookRepository->deleteBook($id);

        if ($isDeleted) {
            return [
                "message" => "Book deleted successfully"
            ];
        } else {
            return [
                'message' => 'Book not found'
            ];
        }
    }

    public function generateCatalog($cursor): array
    {
        $limit = 100;

        $books = $this->bookRepository->getBooksByCursor($cursor, $limit);

        $catalogData = [];
        foreach ($books as $book) {
            $catalogData[] = [
                'title' => $book->getTitle(),
                'price' => $book->getPrice()
            ];
        }

        $lastBookId = null;
        if (count($books) > 0) {
            $lastBook = end($books);
            $lastBookId = $lastBook->getId();
        }

        return [
            'catalogData' => $catalogData,
            'nextCursor' => $lastBookId
        ];
    }

}