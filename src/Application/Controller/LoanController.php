<?php

namespace App\Application\Controller;


use App\Application\Models\DB;
use PDO;
use Slim\Psr7\Request;
use Slim\Psr7\Response;
use Respect\Validation\Validator as v;

class LoanController
{
    private $db;

    public function __construct(DB $db)
    {
        $this->db = $db;
    }

    public function index(Request $request, Response $response): Response
    {
        // get query params
        $query = $request->getQueryParams();
        $ktp = $query['ktp'] ?? null;
        $name = $query['name'] ?? null;

        // init query
        $conn = $this->db->connect();


        // Conditional Query Paramas
        if ($ktp === null && $name === null) {
            $stmt = $conn->query("SELECT * FROM loans");
        } else if ($ktp === null) {
            $stmt = $conn->prepare("SELECT * FROM loans WHERE name LIKE ?");
            $stmt->execute(['%' . $name . '%']);
        } else if ($name === null) {
            $stmt = $conn->prepare("SELECT * FROM loans WHERE ktp = ?");
            $stmt->execute([$ktp]);
        } else {
            $stmt = $conn->prepare("SELECT * FROM loans WHERE ktp = ? AND name LIKE ?");
            $stmt->execute([$ktp, '%' . $name . '%']);
        }

        $data = $stmt->fetchAll(PDO::FETCH_OBJ);

        // return response
        $data = [
            "error" => false,
            "message" => "Success Get All Loan",
            "data" => $data
        ];
        $response->getBody()->write(json_encode($data));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }

    public function create(Request $request, Response $response)
    {

        // get request body
        $data = $request->getParsedBody();

        // custom error validator ktp
        $customError = v::callback(function ($ktp) use ($data) {
            $dob = $data['date_of_birth'];
            $sex = $data['sex'];
            // var_dump($ktp);

            $year = substr($dob, 0, 4);
            $month = substr($dob, 5, 2);
            $day = (int)substr($dob, 8, 2);


            if ($sex == "female") {
                $day += 40;
            }

            $expectedFormat = "XXXXXX" . str_pad($day, 2, "0", STR_PAD_LEFT) . $month . $year . "XXXXXX";
            // print_r($expectedFormat);
            return (substr($ktp, 6, 8) == substr($expectedFormat, 6, 8));
        });
        // var_dump($data);

        // init validator
        $errors = [];

        // rules validator
        $validator = v::arrayType()
            ->key('first_name', v::stringType()->length(4, 255)->regex('/[A-Za-z]/'))
            ->key('last_name', v::stringType()->length(4, 255)->regex('/[a-z]/'))
            ->key('ktp', v::stringType()->length(16, 16)->callback($customError))
            ->key('loan_amount', v::number()->min(1000)->max(10000))
            ->key('loan_periode', v::number()->min(1)->max(120))
            ->key('loan_purpose', v::stringType()->length(4, 255)->anyOf(
                v::contains('vacation'),
                v::contains('renovation'),
                v::contains('electronics'),
                v::contains('wedding'),
                v::contains('car'),
                v::contains('rent'),
                v::contains('investment')
            ))
            ->key("date_of_birth", v::date("Y-m-d"))
            ->key('sex', v::stringType()->in(['male', 'female']));


        try {
            $validator->assert($data);
        } catch (\Respect\Validation\Exceptions\NestedValidationException $e) {
            $response->getBody()->write(json_encode(["error" => true, "message" => "Failed Create Loan", "data" => $e->getMessages()]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        // check validation
        if (!empty($errors)) {
            $response->getBody()->write(json_encode(["error" => true,  "data" => $errors]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        // Get Connect Database
        $conn = $this->db->connect();

        // Query SQL for the insert data loan
        $sql = $conn->prepare("INSERT INTO loans (name, ktp, loan_amount, loan_periode, loan_purpose, date_of_birth, sex) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $sql->execute([$data['first_name'] . ' ' . $data['last_name'], $data['ktp'], $data['loan_amount'], $data['loan_periode'], $data['loan_purpose'], $data['date_of_birth'], $data['sex']]);

        //Get Id yang di insert 
        $loanId = $conn->lastInsertId();

        // Get data loan 
        $stmt = $conn->prepare("SELECT * FROM loans WHERE id = ?");
        $stmt->execute([$loanId]);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $loan = $stmt->fetch();


        // return response
        $response->getBody()->write(json_encode(["error" => false, "message" => "Loan succesfully created", "data" => $loan]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
    }
}
