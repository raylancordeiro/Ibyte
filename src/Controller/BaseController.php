<?php

namespace App\Controller;

use App\Api\ApiException;
use App\Api\ApiProblemException;
use JMS\Serializer\SerializationContext;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class BaseController extends AbstractController
{
    /**
     * Processa os dados recebidos e submete um formulário
     *
     * @param Request $request
     * @param FormInterface $form
     * @throws ApiProblemException se o JSON recebido na requisição não for válido
     */
    protected function processForm(Request $request, FormInterface $form)
    {
        if ($request->getMethod() == 'GET') {
            $data = $request->query->all();
        } else {
            $data = $this->getParsedBody($request);
            if ($data === null) {
                $apiProblem = new ApiException(422, ApiException::TYPE_INVALID_REQUEST_BODY_FORMAT);
                throw new ApiProblemException($apiProblem);
            }
        }
        $clearMissing = $request->getMethod() != 'PATCH';
        $form->submit($data, $clearMissing);
    }

    /**
     * Retorna o corpo de uma requisição em formato array
     *
     * @param Request $request
     * @return mixed
     */
    protected function getParsedBody(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        return $data;
    }

    /**
     * Valida um formulário e lança uma exceção que interrompe a requisição, caso não seja válido
     *
     * @param FormInterface $form
     * @param Request $request
     */
    protected function validateForm(FormInterface $form, Request $request)
    {
        $this->processForm($request, $form);
        if (!$form->isValid()) {
            $this->throwApiProblemValidationException($form);
        }
    }

    /**
     * Cria uma resposta da Exceção no formato Json
     *
     * @param string $message
     * @param ApiException $exception
     * @return Response
     */
    protected function createApiException(string $message, ApiException $exception)
    {
        $code = $exception->getStatusCode() === 0 ? 409 : $exception->getStatusCode();
        $json = $this->serialize([
            'error' => [
                'message' => empty($exception->getMessage()) ? $message : $exception->getMessage(),
                'details' => $exception->getExtraData()
            ]
        ]);
        return new Response($json, $code, [
            'Content-Type' => 'application/json'
        ]);
    }

    /**
     * Cria uma resposta padronizada para a API
     *
     * @param mixed $data       Dados a serem serializados e retornados
     * @param int   $statusCode HTTP status code
     * @return Response
     */
    protected function createApiResponse($data, $statusCode = 200)
    {
        $json = $this->serialize($data);
        return new Response($json, $statusCode, [
            'Content-Type' => 'application/json'
        ]);
    }

    /**
     * Serializa um dado no formato desejado
     *
     * @param mixed  $data   Dado a ser serializado
     * @param string $format Formato desejado
     * @return mixed Dado serializado no formato desejado
     */
    protected function serialize($data, $format = 'json')
    {
        $context = new SerializationContext();
        $context->setSerializeNull(true);

        $request = $this->get('request_stack')->getCurrentRequest();
        $groups = ['Default'];
        if ($request->query->getBoolean('deep')) {
            $groups[] = 'deep';
        }
        $context->setGroups($groups);
        $normalizer = new ObjectNormalizer();
        $encoder = new JsonEncoder();

        $serializer = new Serializer([$normalizer], [$encoder]);
        return $serializer->serialize($data, $format, [AbstractNormalizer::IGNORED_ATTRIBUTES => [
            '__initializer__',
            '__cloner__',
            '__isInitialized__']]);
    }

    /**
     * Prepara e executa o lançamento de uma exceção apropriada para
     * um problema de validação encontrado em um formulário
     *
     * @param FormInterface $form Formulário inválido
     * @throws ApiProblemException
     */
    protected function throwApiProblemValidationException(FormInterface $form)
    {
        $errors = $this->getErrorsFromForm($form);
        $apiProblem = new ApiException(Response::HTTP_UNPROCESSABLE_ENTITY, ApiException::TYPE_VALIDATION_ERROR);
        $apiProblem->set('errors', $errors);
        throw $apiProblem;
    }

    /**
     * Reúne os erros encontrados em um formulário
     *
     * @param FormInterface $form Formulário inválido a ter os erros reunidos
     * @return array erros encontrados no formulário
     */
    protected function getErrorsFromForm(FormInterface $form)
    {
        $errors = [];
        foreach ($form->getErrors() as $error) {
            $errors[] = $error->getMessage();
        }
        foreach ($form->all() as $childForm) {
            if ($childForm instanceof FormInterface) {
                if ($childErrors = $this->getErrorsFromForm($childForm)) {
                    $errors[$childForm->getName()] = $childErrors;
                }
            }
        }
        return $errors;
    }
}