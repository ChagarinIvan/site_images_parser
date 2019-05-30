<?php

namespace App\Clients;

use App\Contracts\Client;
use App\Exceptions\ClientException;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Response;

class GuzzleClient implements Client
{
    /**
     * @var \App\Clients\GuzzleClient
     */
    private $htmlClient;

    public function __construct()
    {
        $this->htmlClient = new HttpClient();
    }

    /**
     * @param string $url
     * @return string
     * @throws \App\Exceptions\ClientException
     */
    public function getPage(string $url): string
    {
        try {
            $request = $this->htmlClient->get($url);

            if ($request->getStatusCode() === Response::HTTP_OK) {
                return $request->getBody()->getContents();
            }

            return '';
        }
        catch (RequestException $ex) {
            return '';
        }
        catch (\Exception $ex) {
            throw new ClientException($ex->getMessage());
        }
    }
}
