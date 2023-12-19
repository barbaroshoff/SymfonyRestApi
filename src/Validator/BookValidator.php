<?php

namespace App\Validator;

use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints as Assert;

class BookValidator
{
    private ValidatorInterface $validator;

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    public function validateBook(array $data): array
    {
        $bookData = array_diff_key($data, array_flip(['authorSearch', 'limit', 'offset']));

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

        $violations = $this->validator->validate($bookData, $constraints);

        $errors = [];
        foreach ($violations as $violation) {
            $errors[$violation->getPropertyPath()][] = $violation->getMessage();
        }

        return $errors;
    }
}
