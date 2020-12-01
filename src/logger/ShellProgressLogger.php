<?php declare(strict_types = 1);
namespace TheSeer\phpDox;

use SebastianBergmann\Timer\ResourceUsageFormatter;
use SebastianBergmann\Timer\Timer;

/**
 * Shell output based logger
 */
class ShellProgressLogger implements ProgressLogger {
    /**
     * @var array
     */
    private $stateChars;

    /**
     * @var int
     */
    private $totalCount = 0;

    /**
     * @var array
     */
    private $stateCount = [
        'processed' => 0,
        'cached'    => 0,
        'failed'    => 0
    ];

    /**
     * @var Timer
     */
    private $timer;

    /**
     * @param string $processed
     * @param string $cached
     * @param string $failed
     */
    public function __construct($processed = '.', $cached = 'c', $failed = 'f') {
        $this->stateChars = [
            'processed' => $processed,
            'cached'    => $cached,
            'failed'    => $failed
        ];
        $this->timer = new Timer();
        $this->timer->start();
    }

    /**
     * @param $state
     *
     * @throws ProgressLoggerException
     */
    public function progress($state): void {
        if (!isset($this->stateChars[$state])) {
            throw new ProgressLoggerException("Unkown progress state '$state'", ProgressLoggerException::UnknownState);
        }
        $this->stateCount[$state]++;
        $this->totalCount++;

        print $this->stateChars[$state];

        if ($this->totalCount % 50 == 0) {
            print "\t[" . $this->totalCount . "]\n";
        }
    }

    public function reset(): void {
        $this->totalCount = 0;
        $this->stateCount = [
            'processed' => 0,
            'cached'    => 0,
            'failed'    => 0
        ];
        $this->timer->start();
    }

    public function completed(): void {
        $pad = (\ceil($this->totalCount / 50) * 50) - $this->totalCount;

        if ($pad != 0) {
            print \str_pad('', (int)$pad, ' ') . "\t[" . $this->totalCount . "]\n";
        }
        print "\n";
    }

    /**
     * @param $msg
     */
    public function log($msg): void {
        if (\func_num_args() > 1) {
            $msg = \vsprintf($msg, \array_slice(\func_get_args(), 1));
        }
        print '[' . \date('d.m.Y - H:i:s') . '] ' . $msg . "\n";
    }

    public function buildSummary(): void {
        print "\n\n";
        print (new ResourceUsageFormatter)->resourceUsage($this->timer->stop());
        print "\n\n";
    }
}
