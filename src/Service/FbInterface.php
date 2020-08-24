<?php

namespace App\Service;

interface FbInterface {
    public function getGraphUser();
    public function getNewUser($id);
}