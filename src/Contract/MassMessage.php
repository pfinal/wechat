<?php

namespace PFinal\Wechat\Contract;

interface MassMessage extends SendMessage
{
    //PHP5.3: Can't inherit abstract function PFinal\Wechat\Contract\MassMessage::jsonData() (previously declared abstract in PFinal\Wechat\Contract\SendMessage)
    //public function jsonData();
}