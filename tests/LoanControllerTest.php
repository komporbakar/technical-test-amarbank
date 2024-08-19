<?php

use PHPUnit\Framework\TestCase;
use Slim\Psr7\Factory\ServerRequestFactory;
use Slim\Psr7\Response;
use App\Application\Controller\LoanController;
use App\Application\Models\DB;

class LoanControllerTest extends TestCase
{
    protected $db;
    protected $requestFactory;



    protected function setUp(): void
    {
        $this->db = new DB();
        $this->requestFactory = new ServerRequestFactory();
    }

    protected function tearDown(): void
    {

        // Delete data from database after each test
        $this->db->executeQuery("DELETE FROM loans WHERE ktp IN ('6521332406200020', '6521336406200020')");
    }


    public function testSuccessCreateLoanSexMale()
    {
        // Hit the controller
        $request = $this->requestFactory->createServerRequest('POST', '/loans');
        // Set the request body
        $request = $request->withParsedBody([
            'first_name' => 'John',
            'last_name' => 'Doel',
            'ktp' => '6521332406200020',
            'loan_amount' => 2300,
            'loan_periode' => 12,
            'loan_purpose' => 'Vacation in duty',
            'date_of_birth' => '2000-06-24',
            'sex' => 'male',
        ]);

        $response = new Response();
        $controller = new LoanController($this->db);

        // Call the controller
        $result = $controller->create($request, $response);
        $responseData = json_decode((string)$result->getBody(), true);

        $this->assertFalse($responseData['error']);

        // Assert the response is what we expected
        $this->assertEquals('Loan succesfully created', $responseData['message']);

        // Expect the response to have the correct data
        $this->assertArrayHasKey('id', $responseData['data']);
        $this->assertEquals('John Doel', $responseData['data']['name']);
        $this->assertEquals('6521332406200020', $responseData['data']['ktp']);
        $this->assertEquals(2300, $responseData['data']['loan_amount']);
        $this->assertEquals(12, $responseData['data']['loan_periode']);
        $this->assertEquals('Vacation in duty', $responseData['data']['loan_purpose']);
        $this->assertEquals('2000-06-24', $responseData['data']['date_of_birth']);
        $this->assertEquals('male', $responseData['data']['sex']);
    }

    public function testSuccessCreateLoanSexFemaleSuccessfully()
    {
        $request = $this->requestFactory->createServerRequest('POST', '/loans');
        $request = $request->withParsedBody([
            'first_name' => 'Arsy',
            'last_name' => 'Doel',
            'ktp' => '6521336406200020',
            'loan_amount' => 2300,
            'loan_periode' => 12,
            'loan_purpose' => 'Vacation in duty',
            'date_of_birth' => '2000-06-24',
            'sex' => 'female',
        ]);
        $response = new Response();
        $controller = new LoanController($this->db);
        $result = $controller->create($request, $response);
        $responseData = json_decode((string)$result->getBody(), true);
        $this->assertFalse($responseData['error']);
        $this->assertEquals('Loan succesfully created', $responseData['message']);
    }

    public function testFailedCreateLoanNameInvalid()
    {
        $request = $this->requestFactory->createServerRequest('POST', '/loans');
        $request = $request->withParsedBody([
            'first_name' => 'Ars',
            'last_name' => 'Doel',
            'ktp' => '6521332406200020',
            'loan_amount' => 23000,
            'loan_periode' => 12,
            'loan_purpose' => 'Vacation in duty',
            'date_of_birth' => '2000-06-24',
            'sex' => 'male',
        ]);
        $response = new Response();
        $controller = new LoanController($this->db);
        $result = $controller->create($request, $response);
        $responseData = json_decode((string)$result->getBody(), true);
        $this->assertTrue($responseData['error']);
        $this->assertEquals('Failed Create Loan', $responseData['message']);
        $this->assertEquals('first_name must have a length between 4 and 255', $responseData['data']['first_name']);
    }

    public function testFailedCreateLoanKtpInvalid()
    {
        $request = $this->requestFactory->createServerRequest('POST', '/loans');
        $request = $request->withParsedBody([
            'first_name' => 'John',
            'last_name' => 'Doel',
            'ktp' => '652133240620002',
            'loan_amount' => 23000,
            'loan_periode' => 12,
            'loan_purpose' => 'Vacation in duty',
            'date_of_birth' => '2000-06-24',
            'sex' => 'male',
        ]);
        $response = new Response();
        $controller = new LoanController($this->db);
        $result = $controller->create($request, $response);
        $responseData = json_decode((string)$result->getBody(), true);
        $this->assertTrue($responseData['error']);
        $this->assertEquals('Failed Create Loan', $responseData['message']);
        $this->assertEquals('ktp must have a length of 16', $responseData['data']['ktp']);
    }

    public function testFailedCreateLoanLoanAmountUnder1000()
    {
        $request = $this->requestFactory->createServerRequest('POST', '/loans');
        $request = $request->withParsedBody([
            'first_name' => 'John',
            'last_name' => 'Doel',
            'ktp' => '6521332406200020',
            'loan_amount' => 900,
            'loan_periode' => 12,
            'loan_purpose' => 'Vacation in duty',
            'date_of_birth' => '2000-06-24',
            'sex' => 'male',
        ]);
        $response = new Response();
        $controller = new LoanController($this->db);
        $result = $controller->create($request, $response);
        $responseData = json_decode((string)$result->getBody(), true);
        $this->assertTrue($responseData['error']);
        $this->assertEquals('Failed Create Loan', $responseData['message']);
        $this->assertEquals('loan_amount must be greater than or equal to 1000', $responseData['data']['loan_amount']);
    }

    public function testFailedCreateLoanLoanAmountOver10000()
    {
        $request = $this->requestFactory->createServerRequest('POST', '/loans');
        $request = $request->withParsedBody([
            'first_name' => 'John',
            'last_name' => 'Doel',
            'ktp' => '6521332406200020',
            'loan_amount' => 90000,
            'loan_periode' => 12,
            'loan_purpose' => 'Vacation in duty',
            'date_of_birth' => '2000-06-24',
            'sex' => 'male',
        ]);
        $response = new Response();
        $controller = new LoanController($this->db);
        $result = $controller->create($request, $response);
        $responseData = json_decode((string)$result->getBody(), true);
        $this->assertTrue($responseData['error']);
        $this->assertEquals('Failed Create Loan', $responseData['message']);
        $this->assertEquals('loan_amount must be less than or equal to 10000', $responseData['data']['loan_amount']);
    }

    public function testFailedCreateLoanSexMaleNotMatchBetweenKtpWithBirthDate()
    {
        $request = $this->requestFactory->createServerRequest('POST', '/loans');
        $request = $request->withParsedBody([
            'first_name' => 'John',
            'last_name' => 'Doel',
            'ktp' => '6521332408200020',
            'loan_amount' => 23000,
            'loan_periode' => 12,
            'loan_purpose' => 'Vacation in duty',
            'date_of_birth' => '2000-06-24',
            'sex' => 'male',
        ]);
        $response = new Response();
        $controller = new LoanController($this->db);
        $result = $controller->create($request, $response);
        $responseData = json_decode((string)$result->getBody(), true);
        $this->assertTrue($responseData['error']);
        $this->assertEquals('Failed Create Loan', $responseData['message']);
        $this->assertEquals('ktp must be valid', $responseData['data']['ktp']);
    }

    public function testFailedCreateLoanSexFemaleNotMatchBetweenKtpWithBirthDate()
    {
        $request = $this->requestFactory->createServerRequest('POST', '/loans');
        $request = $request->withParsedBody([
            'first_name' => 'John',
            'last_name' => 'Doel',
            'ktp' => '6521332408200020',
            'loan_amount' => 23000,
            'loan_periode' => 12,
            'loan_purpose' => 'Vacation in duty',
            'date_of_birth' => '2000-06-24',
            'sex' => 'female',
        ]);
        $response = new Response();
        $controller = new LoanController($this->db);
        $result = $controller->create($request, $response);
        $responseData = json_decode((string)$result->getBody(), true);
        $this->assertTrue($responseData['error']);
        $this->assertEquals('Failed Create Loan', $responseData['message']);
        $this->assertEquals('ktp must be valid', $responseData['data']['ktp']);
    }

    public function testFailedCreateLoanSexNotFound()
    {
        $request = $this->requestFactory->createServerRequest('POST', '/loans');
        $request = $request->withParsedBody([
            'first_name' => 'John',
            'last_name' => 'Doel',
            'ktp' => '6521332406200020',
            'loan_amount' => 23000,
            'loan_periode' => 12,
            'loan_purpose' => 'Vacation in duty',
            'date_of_birth' => '2000-06-24',
            'sex' => 'sex',
        ]);
        $response = new Response();
        $controller = new LoanController($this->db);
        $result = $controller->create($request, $response);
        $responseData = json_decode((string)$result->getBody(), true);
        $this->assertTrue($responseData['error']);
        $this->assertEquals('Failed Create Loan', $responseData['message']);
        $this->assertEquals('sex must be in `{ "male", "female" }`', $responseData['data']['sex']);
    }

    public function testSuccessGetAllLoans()
    {
        $request = $this->requestFactory->createServerRequest('GET', '/loans');
        $response = new Response();

        $controller = new LoanController($this->db);
        $result = $controller->index($request, $response);

        $responseData = json_decode((string)$result->getBody(), true);

        $this->assertFalse($responseData['error']);
        $this->assertEquals('Success Get All Loan', $responseData['message']);
    }

    public function testSuccessGetLoanByQueryParamsName()
    {
        $request = $this->requestFactory->createServerRequest('GET', '/loans');
        $request = $request->withQueryParams(['name' => 'John']);
        $response = new Response();

        $controller = new LoanController($this->db);
        $result = $controller->index($request, $response);

        $responseData = json_decode((string)$result->getBody(), true);
        $this->assertFalse($responseData['error']);
        $this->assertEquals('Success Get All Loan', $responseData['message']);
    }

    public function testSuccessGetLoanByQueryParamsKtp()
    {
        $request = $this->requestFactory->createServerRequest('GET', '/loans');
        $request = $request->withQueryParams(['ktp' => '6521332408200020']);
        $response = new Response();

        $controller = new LoanController($this->db);
        $result = $controller->index($request, $response);

        $responseData = json_decode((string)$result->getBody(), true);
        $this->assertFalse($responseData['error']);
        $this->assertEquals('Success Get All Loan', $responseData['message']);
    }
}
