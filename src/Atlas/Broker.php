<?php
/**
 * This file is part of the Atlas package
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace DecodeLabs\Atlas;

use DecodeLabs\Atlas\Channel;
use DecodeLabs\Atlas\Channel\Stream;
use DecodeLabs\Atlas\Channel\Buffer;

class Broker implements Channel
{
    protected $input = [];
    protected $output = [];
    protected $error = [];

    /**
     * Add channel on input endpoint
     */
    public function addInputChannel(Channel $channel): Broker
    {
        $id = spl_object_id($channel);
        $this->input[$id] = $channel;

        return $this;
    }

    /**
     * Is channel registered on input endpoint?
     */
    public function hasInputChannel(Channel $channel): bool
    {
        $id = spl_object_id($channel);
        return isset($this->input[$id]);
    }

    /**
     * Remove channel from input endpoint
     */
    public function removeInputChannel(Channel $channel): Broker
    {
        $id = spl_object_id($channel);
        unset($this->input[$id]);
        return $this;
    }

    /**
     * Get list of input channels
     */
    public function getInputChannels(): array
    {
        return $this->input;
    }

    /**
     * Get first input channel
     */
    public function getFirstInputChannel(): ?Channel
    {
        foreach ($this->input as $channel) {
            return $channel;
        }

        return null;
    }


    /**
     * Add channel on output endpoint
     */
    public function addOutputChannel(Channel $channel): Broker
    {
        $id = spl_object_id($channel);
        $this->output[$id] = $channel;

        return $this;
    }

    /**
     * Is channel registered on input endpoint?
     */
    public function hasOutputChannel(Channel $channel): bool
    {
        $id = spl_object_id($channel);
        return isset($this->output[$id]);
    }

    /**
     * Remove channel from output endpoint
     */
    public function removeOutputChannel(Channel $channel): Broker
    {
        $id = spl_object_id($channel);
        unset($this->output[$id]);
        return $this;
    }

    /**
     * Get list of output channels
     */
    public function getOutputChannels(): array
    {
        return $this->output;
    }

    /**
     * Get first output channel
     */
    public function getFirstOutputChannel(): ?Channel
    {
        foreach ($this->output as $channel) {
            return $channel;
        }

        return null;
    }


    /**
     * Add channel on error endpoint
     */
    public function addErrorChannel(Channel $channel): Broker
    {
        $id = spl_object_id($channel);
        $this->error[$id] = $channel;

        return $this;
    }

    /**
     * Is channel registered at error endpoint?
     */
    public function hasErrorChannel(Channel $channel): bool
    {
        $id = spl_object_id($channel);
        return isset($this->error[$id]);
    }

    /**
     * Remove channel from error endpoint
     */
    public function removeErrorChannel(Channel $channel): Broker
    {
        $id = spl_object_id($channel);
        unset($this->error[$id]);
        return $this;
    }

    /**
     * Get list of error channels
     */
    public function getErrorChannels(): array
    {
        return $this->error;
    }

    /**
     * Get first error channel
     */
    public function getFirstErrorChannel(): ?Channel
    {
        foreach ($this->error as $channel) {
            return $channel;
        }

        return null;
    }



    /**
     * Add channel to input and output endpoints
     */
    public function addIoChannel(Channel $channel): Broker
    {
        $id = spl_object_id($channel);

        $this->input[$id] = $channel;
        $this->output[$id] = $channel;

        return $this;
    }

    /**
     * Add channel to all endpoints
     */
    public function addChannel(Channel $channel): Broker
    {
        $id = spl_object_id($channel);

        $this->input[$id] = $channel;
        $this->output[$id] = $channel;
        $this->error[$id] = $channel;

        return $this;
    }

    /**
     * Is channel in any endpoint
     */
    public function hasChannel(Channel $channel): bool
    {
        $id = spl_object_id($channel);

        return
            isset($this->input[$id]) ||
            isset($this->output[$id]) ||
            isset($this->error[$id]);
    }

    /**
     * Remove channel from all endpoints
     */
    public function removeChannel(Channel $channel): Broker
    {
        $id = spl_object_id($channel);
        unset($this->input[$id]);
        unset($this->output[$id]);
        unset($this->error[$id]);
        return $this;
    }


    /**
     * Get channel resource
     */
    public function getResource()
    {
        return null;
    }


    /**
     * Set all input channels as blocking
     */
    public function setBlocking(bool $flag): Channel
    {
        foreach ($this->input as $channel) {
            $channel->setBlocking($flag);
        }

        return $this;
    }

    /**
     * Any any input channels blocking?
     */
    public function isBlocking(): bool
    {
        foreach ($this->input as $channel) {
            if ($channel->isBlocking()) {
                return true;
            }
        }

        return false;
    }


    /**
     * Are any input channels readable?
     */
    public function isReadable(): bool
    {
        foreach ($this->input as $channel) {
            if ($channel->isReadable()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Read $length from first readable input channel
     */
    public function read(int $length): ?string
    {
        foreach ($this->input as $channel) {
            if (!$channel->isReadable()) {
                continue;
            }

            if (null !== ($line = $channel->read($length))) {
                return $line;
            }
        }

        return null;
    }

    /**
     * Read all from first readable input channel
     */
    public function readAll(): ?string
    {
        foreach ($this->input as $channel) {
            if (!$channel->isReadable()) {
                continue;
            }

            if (null !== ($line = $channel->readAll())) {
                return $line;
            }
        }

        return null;
    }

    /**
     * Read line from first readable input channel
     */
    public function readLine(): ?string
    {
        foreach ($this->input as $channel) {
            if (!$channel->isReadable()) {
                continue;
            }

            if (null !== ($line = $channel->readLine())) {
                return $line;
            }
        }

        return null;
    }

    /**
     * Read all available data from input channels and pass to external channel
     */
    public function readTo(Channel $writer): Channel
    {
        while (!$this->isAtEnd()) {
            $chunk = $this->read(8192);

            if ($chunk === null) {
                break;
            }

            $writer->write($chunk);
        }

        return $this;
    }


    /**
     * Are any output channels writable?
     */
    public function isWritable(): bool
    {
        foreach ($this->output as $channel) {
            if ($channel->isWritable()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Write data, limit of $length, to output channels
     */
    public function write(?string $data, int $length=null): int
    {
        if ($length === 0) {
            return 0;
        } elseif ($length === null) {
            $length = strlen($data);
        }

        foreach ($this->output as $channel) {
            if (!$channel->isWritable()) {
                continue;
            }

            for ($written = 0; $written < $length; $written += $result) {
                $result = $channel->write(substr($data, $written), $length - $written);

                if ($result === null) {
                    throw Glitch::EOverflow('Could not write buffer to output', null, $data);
                }
            }
        }

        return $length;
    }

    /**
     * Write line to error channels
     */
    public function writeLine(?string $data=''): int
    {
        return $this->write($data.PHP_EOL);
    }

    /**
     * Write buffer to output channels
     */
    public function writeBuffer(Buffer $buffer, int $length): int
    {
        return $this->write($buffer->read($length), $length);
    }


    /**
     * Are any error channels writable?
     */
    public function isErrorWritable(): bool
    {
        foreach ($this->error as $channel) {
            if ($channel->isWritable()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Write data, limit of $length, to error channels
     */
    public function writeError(?string $data, int $length=null): int
    {
        if ($length === 0) {
            return 0;
        } elseif ($length === null) {
            $length = strlen($data);
        }

        foreach ($this->error as $channel) {
            if (!$channel->isWritable()) {
                continue;
            }

            for ($written = 0; $written < $length; $written += $result) {
                $result = $channel->write(substr($data, $written), $length - $written);

                if ($result === null) {
                    throw Glitch::EOverflow('Could not write buffer to output', null, $data);
                }
            }
        }

        return $length;
    }

    /**
     * Write line to error channels
     */
    public function writeErrorLine(?string $data=''): int
    {
        return $this->writeError($data.PHP_EOL);
    }

    /**
     * Write buffer to error channels
     */
    public function writeErrorBuffer(Buffer $buffer, int $length): int
    {
        return $this->writeError($buffer->read($length), $length);
    }


    /**
     * Are all input channels at end?
     */
    public function isAtEnd(): bool
    {
        foreach ($this->input as $channel) {
            if (!$channel->isAtEnd()) {
                return false;
            }
        }

        return true;
    }

    /**
     * Close all channels
     */
    public function close(): Channel
    {
        foreach ($this->input as $channel) {
            $channel->close();
        }

        foreach ($this->output as $channel) {
            $channel->close();
        }

        foreach ($this->error as $channel) {
            $channel->close();
        }

        return $this;
    }
}
