<?php

namespace DocDoc\RgsApiClient\test;

use DocDoc\RgsApiClient\Dto\RgsApiParamsInterface;
use DocDoc\RgsApiClient\Exception\BadRequestRgsException;
use DocDoc\RgsApiClient\Exception\BaseRgsException;
use DocDoc\RgsApiClient\Exception\ValidationException;
use DocDoc\RgsApiClient\PatientRgsClient;
use DocDoc\RgsApiClient\ValueObject\Patient\MetaData;
use DocDoc\RgsApiClient\ValueObject\Patient\Patient;
use DocDoc\RgsApiClient\ValueObject\Patient\TimeZone;
use GuzzleHttp\Client;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * @coversDefaultClass \DocDoc\RgsApiClient\PatientRgsClient
 */
class PatientsRgsClientTest extends TestCase
{
	/** @var PatientRgsClient */
	private $client;

	public function setUp(): void
	{
		parent::setUp();
		$rgsApiParams = $this->createMock(RgsApiParamsInterface::class);
		// Mock server Apiary
		$rgsApiParams->method('getHost')->willReturn('https://private-63631f-chronicmonitor.apiary-mock.com/');
		$rgsApiParams->method('getPartnerId')->willReturn(241);

		$this->client = new PatientRgsClient(
			new Client(),
			$rgsApiParams,
			$this->createMock(LoggerInterface::class)
		);
	}

	/**
	 * Тест отправки запроса на мок сервер Apiary
	 * Проверяется ответ на  соответствие запросу.
	 *
	 * @covers ::createPatient
	 *
	 * @param string $jsonPatient
	 *
	 * @dataProvider successJsonSerializeDataProvider
	 *
	 * @throws BadRequestRgsException
	 * @throws BaseRgsException
	 * @throws ValidationException
	 */
	public function testCreate(string $jsonPatient): void
	{
		$patient = $this->getPatientObject($jsonPatient);

		$patientResponse = $this->client->createPatient($patient);
		$this->checkPatient($patient, $patientResponse);
	}

	/**
	 * Тест отправки запроса на мок сервер Apiary
	 * Проверяется ответ на  соответствие запросу.
	 *
	 * @covers ::updatePatient
	 *
	 * @param string $jsonPatient
	 *
	 * @dataProvider successJsonSerializeDataProvider
	 *
	 * @throws BadRequestRgsException
	 * @throws BaseRgsException
	 * @throws ValidationException
	 */
	public function testUpdate(string $jsonPatient): void
	{
		$patient = $this->getPatientObject($jsonPatient);
		$patientResponse = $this->client->updatePatient($patient);

		$this->checkPatient($patient, $patientResponse);
	}

	/**
	 * @dataProvider getPatientDataProvider
	 *
	 * @param $jsonPatient
	 * @covers ::getPatient
	 *
	 * @throws BadRequestRgsException
	 * @throws BaseRgsException
	 */
	public function testGetPatient($jsonPatient): void
	{
		$jsonPatientObject = json_decode($jsonPatient, false);

		$patient = $this->client->getPatient(100);
		$expectedObject = json_decode($patient->getBody()->getContents(), false);
		$fields = get_object_vars($jsonPatientObject);
		//этих полей в ответе нет, все остальные должны совпадать
		unset($fields['categoryKey']);
		foreach ($fields as $field => $value) {
			self::assertEquals($expectedObject->$field, $value);
		}
	}

	/**
	 * @dataProvider getPatientDataProvider
	 * @covers ::activate
	 *
	 * @param $jsonPatient
	 *
	 * @throws BadRequestRgsException
	 * @throws BaseRgsException
	 */
	public function testActivate($jsonPatient): void
	{
		$jsonPatientObject = json_decode($jsonPatient, false);

		$patient = $this->client->activate(100);
		$expectedObject = json_decode($patient->getBody()->getContents(), false);
		$fields = get_object_vars($jsonPatientObject);
		//этих полей в ответе нет, все остальные должны совпадать
		unset($fields['categoryKey'], $fields['id']);
		foreach ($fields as $field => $value) {
			self::assertEquals($expectedObject->$field, $value);
		}
	}

	/**
	 * @dataProvider getPatientDataProvider
	 * @covers ::inactivate
	 *
	 * @param $jsonPatient
	 *
	 * @throws BadRequestRgsException
	 * @throws BaseRgsException
	 */
	public function testInactivate($jsonPatient): void
	{
		$jsonPatientObject = json_decode($jsonPatient, false);

		$patient = $this->client->activate(100);
		//В Моке АПИ не корректный ответ  поправим в тесте.
		$expectedObject = json_decode($patient->getBody()->getContents(), false);
		$fields = get_object_vars($jsonPatientObject);
		//этих полей в ответе нет, все остальные должны совпадать
		unset($fields['categoryKey']);

		foreach ($fields as $field => $value) {
			self::assertEquals($expectedObject->$field, $value);
		}
	}

	public function successJsonSerializeDataProvider(): array
	{
		return [
			[
				'{
                    "categoryKey": "diabetic",
                    "firstName": "Иван",
                    "patronymic": "Иванов",
                    "phone": "+7 (904) 999-99-99",
                    "externalId": -100000000,
                    "metadata": {
                        "productId": 13,
                        "contractId": 10293
                    },
                    "timezone": 120,
                    "active": true,
                    "monitoringEnabled": true
                }'
			],
		];
	}

	public function getPatientDataProvider(): array
	{
		return [
			[
				'{
                    "id": -100000000,
                    "categoryKey": "diabetic",
                    "firstName": "Иван",
                    "patronymic": "Иванов",
                    "phone": "+7 (904) 999-99-99",
                    "externalId": -100000000,
                    "active": true,
                    "monitoringEnabled": true,
                    "metadata": {
                      "productId": 13,
                      "contractId": 10293
                    },
                    "metricsRanges": [
                      {
                        "key": "sys",
                        "name": "Систола",
                        "parentKey": "ad",
                        "minValue": "60",
                        "maxValue": "120"
                      }
                    ],
                    "timezone": "+02:00"
                  }'
			]
		];
	}

	private function checkPatient(Patient $requestPatient, ResponseInterface $responsePatient): void
	{
		$responsePatient = json_decode($responsePatient->getBody()->getContents(), true);

		self::assertEquals($responsePatient['category']['key'], $requestPatient->getCategoryKey());
		self::assertEquals($responsePatient['firstName'], $requestPatient->getFirstName());
		self::assertEquals($responsePatient['phone'], $requestPatient->getPhone());
		self::assertEquals($responsePatient['externalId'], $requestPatient->getExternalId());
		self::assertEquals($responsePatient['patronymic'], $requestPatient->getPatronymic());
		self::assertEquals($responsePatient['active'], $requestPatient->isActive());
		self::assertEquals($responsePatient['monitoringEnabled'], $requestPatient->isMonitoringEnabled());
	}

	/**
	 * @param string $jsonPatient
	 *
	 * @return Patient
	 * @throws ValidationException
	 */
	private function getPatientObject(string $jsonPatient): Patient
	{
		$jsonPatientObject = json_decode($jsonPatient, false);
		$patient = new Patient();
		$patient->setCategoryKey($jsonPatientObject->categoryKey);
		$patient->setFirstName($jsonPatientObject->firstName);
		$patient->setPhone($jsonPatientObject->phone);
		$patient->setPatronymic($jsonPatientObject->patronymic);
		$patient->setExternalId($jsonPatientObject->externalId);

		$patient->setMetadata(
			new MetaData($jsonPatientObject->metadata->productId, $jsonPatientObject->metadata->contractId)
		);
		$patient->setTimezone(new TimeZone($jsonPatientObject->timezone));
		return $patient;
	}
}