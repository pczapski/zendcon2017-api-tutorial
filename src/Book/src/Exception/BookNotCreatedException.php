<?php

namespace Book\Exception;

use DomainException;
use Zend\ProblemDetails\Exception\CommonProblemDetailsException;
use Zend\ProblemDetails\Exception\ProblemDetailsException;

class BookNotCreatedException  extends DomainException implements
    ExceptionInterface,
    ProblemDetailsException
{

    use CommonProblemDetailsException;

    /**
     * BookNotCreatedException constructor.
     * @param string $message
     * @param int $code
     * @param \Exception $previous
     */
    public function __construct($message, $code = 0, \Exception $previous = null)
    {
        $this->status = 500;
        $this->detail = 'An error occurred creating the book in the database';
        $this->title = 'Book create error';
        $this->type = 'review.create.update_error';

        parent::__construct($message, $code, $previous);
    }
}