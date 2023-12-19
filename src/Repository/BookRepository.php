<?php

namespace App\Repository;

use App\Entity\Book;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Book>
 *
 * @method Book|null find($id, $lockMode = null, $lockVersion = null)
 * @method Book|null findOneBy(array $criteria, array $orderBy = null)
 * @method Book[]    findAll()
 * @method Book[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BookRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Book::class);
    }

    public function getBooksByLimitOffset(int $limit, int $offset): array
    {
        return $this->createQueryBuilder('b')
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getQuery()
            ->getResult();
    }

    public function getBooksByAuthor(string $author, int $limit = 10, int $offset = 0): array
    {
        $queryBuilder = $this->createQueryBuilder('b');
        $queryBuilder->where('b.author = :author')
            ->setParameter('author', $author)
            ->setMaxResults($limit)
            ->setFirstResult($offset);

        $query = $queryBuilder->getQuery();
        $books = $query->getResult();

        return $books;
    }

    public function createBook(Book $book): Book
    {
        $entityManager = $this->getEntityManager();
        $entityManager->persist($book);
        $entityManager->flush();

        return $book;
    }

    public function saveBook(Book $book): Book
    {
        $entityManager = $this->getEntityManager();
        $entityManager->persist($book);
        $entityManager->flush();

        return $book;
    }

    public function deleteBook(int $id): bool
    {
        $entityManager = $this->getEntityManager();
        $book = $this->find($id);

        if (!$book) {
            return false;
        }

        $entityManager->remove($book);
        $entityManager->flush();

        return true;
    }

    public function getBooksByCursor($cursor, $limit): array
    {
        $queryBuilder = $this->createQueryBuilder('b');

        if ($cursor !== null) {
            // Логика для использования курсора для выборки данных
            $queryBuilder->where('b.id > :cursor')
                ->setParameter('cursor', $cursor);
        }

        $queryBuilder->orderBy('b.id')
            ->setMaxResults($limit);

        $query = $queryBuilder->getQuery();
        $books = $query->getResult();

        return $books;
    }
}
