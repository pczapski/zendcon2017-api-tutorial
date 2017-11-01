<?php
namespace Book\Model;

use Book\Collection\BookCollection;
use Book\Entity\BookEntity;
use Book\Exception;
use Book\Exception\BookNotCreatedException;
use Book\PdoPaginator;
use Book\PdoService;
use PDOException;
use Ramsey\Uuid\Uuid;

class BookModel
{
    private $pdo;

    public function __construct(PdoService $pdo)
    {
        $this->pdo = $pdo;
    }

    public function getAllBooks(): BookCollection
    {
        $statement = $this->pdo->prepare('SELECT * FROM book LIMIT :limit OFFSET :offset');
        $countStatement = $this->pdo->prepare('SELECT COUNT(id) FROM book');

        return new BookCollection(
            new PdoPaginator($statement, $countStatement, [], BookEntity::class)
        );
    }

    public function getBook(string $id): BookEntity
    {
        $statement = $this->pdo->prepare('SELECT * FROM book WHERE id = :id');
        $statement->execute([':id' => $id]);
        $statement->setFetchMode(PdoService::FETCH_CLASS);

        $book = $statement->fetch();
        if (! $book instanceof BookEntity) {
            throw Exception\BookNotFoundException::forBook($id);
        }
        return $book;
    }

    /**
     * @param BookEntity $book
     * @return BookEntity
     */
    public function create(BookEntity $book) : BookEntity
    {
        $book->id = Uuid::uuid4()->toString();

        $statement = $this->pdo->prepare(
            'INSERT INTO book (id, title, author, publisher, pages, year, isbn)
             VALUES (:id, :title, :author, :publisher, :pages, :year, :isbn)'
        );

        try {
            $statement->execute([
                ':id'       => $book->id,
                ':title' => $book->title,
                ':author'  => $book->author,
                ':publisher'   => $book->publisher,
                ':pages'    => $book->pages,
                ':year' => $book->year,
                ':isbn' => $book->isbn
            ]);
        } catch (PDOException $e) {
            throw new BookNotCreatedException(
                'Could not add review to database',
                (int) $e->getCode(),
                $e
            );
        }

        return $book;
    }
}
