<?php

namespace App\Service;

use App\Entity\Book;
use App\Repository\BookRepository;
use Symfony\Component\HttpFoundation\Request;

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

        $books = $this->bookRepository->getBooksByLimitOffset($limit, $offset);

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

    public function getBooksById($id): array
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

    public function createBook(array $data): Book
    {
        try {
            $book = $this->bookRepository->createBook($data);
            if ($book instanceof Book) {
                return [
                    'success' => true,
                    'message' => 'Книга успешно создана',
                    'book' => $book,
                ];
            }
        } catch (\Exception $e) {

        }

        return [
            'success' => false,
            'message' => 'Не удалось создать книгу',
            'book' => null,
        ];
    }

    public function updateBook($data)
    {

    }

    public function deleteBook($data)
    {

    }

}