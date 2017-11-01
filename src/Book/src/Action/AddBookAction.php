<?php

namespace Book\Action;

use Book\Entity\BookEntity;
use Book\Model\BookModel;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Expressive\Authentication\UserInterface;
use Zend\Expressive\Hal\HalResponseFactory;
use Zend\Expressive\Hal\ResourceGenerator;
use Book\Exception;

class AddBookAction implements MiddlewareInterface
{
    /** @var BookModel */
    private $book;

    /** @var ResourceGenerator */
    private $resourceGenerator;

    /** @var HalResponseFactory */
    private $responseFactory;

    public function __construct(
        BookModel $book,
        ResourceGenerator $resourceGenerator,
        HalResponseFactory $responseFactory
    ) {
        $this->book = $book;
        $this->resourceGenerator = $resourceGenerator;
        $this->responseFactory = $responseFactory;
    }

    /**
     * Process an incoming server request and return a response, optionally delegating
     * to the next middleware component to create the response.
     *
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     *
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $book = $this->createBookFromFilteredData($request->getParsedBody());
        $user = $request->getAttribute(UserInterface::class, false);
        if (false === $user) {
            throw new Exception\UserNotAuthenticatedException();
        }
        $book = $this->book->create($book);
        $resource = $this->resourceGenerator->fromObject($book, $request);

        return $this->responseFactory->createResponse($request, $resource);
    }

    private function createBookFromFilteredData(array $values) : BookEntity
    {
        $book = new BookEntity();

        $book->title   = $values['title'];
        $book->author = $values['author'];
        $book->publisher = $values['publisher'];
        $book->pages = $values['pages'];
        $book->year = $values['year'];
        $book->isbn = $values['isbn'];

        return $book;
    }
}