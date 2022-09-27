<?php

/**
 * @package Atlas
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Atlas\Plugins;

use DecodeLabs\Atlas\Context;
use DecodeLabs\Atlas\File;
use DecodeLabs\Collections\Tree;
use DecodeLabs\Collections\Tree\NativeMutable as NativeTree;
use DecodeLabs\Deliverance\DataReceiver;

use DecodeLabs\Exceptional;

use GuzzleHttp\Client as HttpClient;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class Http
{
    protected Context $context;

    /**
     * Init with context
     */
    public function __construct(Context $context)
    {
        $this->context = $context;
    }

    /**
     * Create new HTTP client
     *
     * @param array<string, mixed> $options
     */
    public function newClient(array $options = []): HttpClient
    {
        if (!class_exists(HttpClient::class)) {
            throw Exceptional::ComponentUnavailable(
                'Cannot create HTTP Client, GuzzleHttp is not installed'
            );
        }

        return new HttpClient($options);
    }

    /**
     * Fetch HTTP URL to memory file
     *
     * @param array<string, mixed> $options
     */
    public function get(
        string $url,
        array $options = []
    ): File {
        $response = $this->newClient()->get($url, $options);
        return $this->importResponse($response);
    }

    /**
     * Fetch HTTP URL to string
     *
     * @param array<string, mixed> $options
     */
    public function getString(
        string $url,
        array $options = []
    ): string {
        $response = $this->newClient()->get($url, $options);
        return (string)$response->getBody();
    }

    /**
     * Fetch HTTL URL and save to disk
     *
     * @param array<string, mixed> $options
     */
    public function getFile(
        string $url,
        string $path,
        array $options = []
    ): File {
        $response = $this->newClient()->get($url, $options);
        return $this->saveResponse($response, $path);
    }

    /**
     * Fetch HTTL URL and save to disk as temp file
     *
     * @param array<string, mixed> $options
     */
    public function getTempFile(
        string $url,
        array $options = []
    ): File {
        $response = $this->newClient()->get($url, $options);
        return $this->saveTempResponse($response);
    }

    /**
     * Fetch json file over HTTP
     *
     * @param array<string, mixed> $options
     * @return Tree<mixed>
     */
    public function getJson(
        string $url,
        array $options = []
    ): Tree {
        if (!class_exists(NativeTree::class)) {
            throw Exceptional::ComponentUnavailable(
                'Cannot expand JSON response without decodelabs/collections'
            );
        }

        $response = $this->newClient()->get($url, $options);
        $json = json_decode((string)$response->getBody(), true);

        if (is_iterable($json)) {
            /** @var iterable<int|string, mixed> $json */
            $output = new NativeTree($json);
        } else {
            $output = new NativeTree(null, $json);
        }

        /** @var Tree<mixed> $output */
        return $output;
    }

    //public function getXml($url, $options): XmlNode;


    /**
     * Save PSR7 response to disk
     */
    public function saveResponse(
        ResponseInterface $response,
        string $path
    ): File {
        $file = $this->context->file($path, 'wb');
        $this->transferStream($response->getBody(), $file);

        $file->close();
        return $file;
    }

    /**
     * Save PSR7 response to disk as temp file
     */
    public function saveTempResponse(ResponseInterface $response): File
    {
        $file = $this->context->newTempFile();
        $this->transferStream($response->getBody(), $file);

        $file->close();
        return $file;
    }

    /**
     * Convert PSR7 response to DataProvider
     */
    public function importResponse(ResponseInterface $response): File
    {
        $file = $this->context->newMemoryFile();
        $this->transferStream($response->getBody(), $file);

        $file->setPosition(0);
        return $file;
    }

    /**
     * Transfer PSR7 stream to DataReceiver
     */
    public function transferStream(
        StreamInterface $stream,
        DataReceiver $receiver
    ): DataReceiver {
        while (!$stream->eof()) {
            $receiver->write($stream->read(8192));
        }

        return $receiver;
    }
}
