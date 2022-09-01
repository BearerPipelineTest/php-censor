<?php

namespace PHPCensor\Plugin;

use HipChat\HipChat;
use PHPCensor\Builder;
use PHPCensor\Common\Exception\InvalidArgumentException;
use PHPCensor\Model\Build;
use PHPCensor\Plugin;

/**
 * Hipchat Plugin
 *
 * @package    PHP Censor
 * @subpackage Application
 *
 * @author James Inman <james@jamesinman.co.uk>
 * @author Dmitry Khomutov <poisoncorpsee@gmail.com>
 */
class HipchatNotify extends Plugin
{
    protected $authToken;
    protected $color;
    protected $notify;
    protected $message;
    protected $room;

    /**
     * @return string
     */
    public static function pluginName()
    {
        return 'hipchat_notify';
    }

    /**
     * {@inheritDoc}
     */
    public function __construct(Builder $builder, Build $build, array $options = [])
    {
        parent::__construct($builder, $build, $options);

        if (!\is_array($options) || !isset($options['room']) || !isset($options['auth_token'])) {
            throw new InvalidArgumentException('Please define room and authToken for hipchat_notify plugin.');
        }

        if (\array_key_exists('auth_token', $options)) {
            $this->authToken = $this->builder->interpolate($options['auth_token'], true);
        }

        $this->room = $options['room'];

        if (isset($options['message'])) {
            $this->message = $options['message'];
        } else {
            $this->message = '%PROJECT_TITLE% built at %BUILD_LINK%';
        }

        if (isset($options['color'])) {
            $this->color = $options['color'];
        } else {
            $this->color = 'yellow';
        }

        if (isset($options['notify'])) {
            $this->notify = $options['notify'];
        } else {
            $this->notify = false;
        }
    }

    /**
     * Run the HipChat plugin.
     * @return bool
     */
    public function execute()
    {
        $hipChat = new HipChat($this->authToken);
        $message = $this->builder->interpolate($this->message);

        $result = true;
        if (\is_array($this->room)) {
            foreach ($this->room as $room) {
                if (!$hipChat->message_room($room, 'PHP Censor', $message, $this->notify, $this->color)) {
                    $result = false;
                }
            }
        } else {
            if (!$hipChat->message_room($this->room, 'PHP Censor', $message, $this->notify, $this->color)) {
                $result = false;
            }
        }

        return $result;
    }
}
