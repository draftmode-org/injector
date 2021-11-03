<?php
namespace Terrazza\Component\Injector\Tests;
use Psr\Log\LoggerInterface;

class LoggerTest implements LoggerInterface {
    private ?string $namespace                      = null;
    private ?string $method                         = null;
    public function withNamespace(string $namespace) : LoggerInterface {
        $logger                                     = clone $this;
        $logger->namespace                          = $namespace;
        return $logger;
    }
    public function withMethod(string $method) : LoggerInterface {
        $logger                                     = clone $this;
        $logger->method                             = $method;
        return $logger;
    }
    private ?int $logLevel;
    public function __construct(int $logLevel=null) {
        $this->logLevel                             = $logLevel;
    }

    /**
     * @param int $logLevel
     * @param string $logLevelName
     * @param $message
     * @param array $context
     */
    private function addMessage(int $logLevel, string $logLevelName, $message, array $context=[]) : void {
        if (!$this->logLevel || $this->logLevel !== $logLevel) {
            return;
        }
        $msg                                        = [];
        if ($this->namespace) {
            $msg[]                                  = $this->namespace;
        }
        if ($this->method) {
            $msg[]                                  = ($this->namespace ? "->" : "").$this->method."()";
        }
        $msg[]                                      = $logLevelName;
        if ($context && array_key_exists("line", $context)) {
            $msg[]                                  = "[line: ".$context["line"]."]";
            unset($context["line"]);
        }
        if (strlen($message)) {
            $msg[]                                  = $message;
        }
        print_r(join(" ", $msg).PHP_EOL);
        if ($context && array_key_exists("arguments", $context)) {
            $arguments                              = $context["arguments"];
            $print                                  = false;
            if (is_array($arguments)) {
                if (count($arguments)) {
                    $print                          = true;
                }
            }
            if ($print) {
                print_r($arguments);
            }
        }
    }

    public function emergency($message, array $context = array()) {
        $this->addMessage(LOG_EMERG, "[emergency]", $message, $context);
    }

    public function alert($message, array $context = array()){
        $this->addMessage(LOG_ALERT, "[alert]", $message, $context);
    }

    public function critical($message, array $context = array()) {
        $this->addMessage(LOG_CRIT, "[critical]", $message, $context);
    }

    public function error($message, array $context = array()) {
        $this->addMessage(LOG_ERR, "[error]", $message, $context);
    }

    public function warning($message, array $context = array()) {
        $this->addMessage(LOG_WARNING, "[warning]", $message, $context);
    }

    public function notice($message, array $context = array()) {
        $this->addMessage(LOG_NOTICE, "[notice]", $message, $context);
    }

    public function info($message, array $context = array()) {
        $this->addMessage(LOG_INFO, "[info]", $message, $context);
    }

    public function debug($message, array $context = array()) {
        $this->addMessage(LOG_DEBUG, "[debug]", $message, $context);
    }

    public function log($level, $message, array $context = array()) {
        $this->addMessage(LOG_NEWS, "[log]", $message, $context);
    }
}