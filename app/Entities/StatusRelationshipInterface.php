<?php

namespace App\Entities;

interface StatusRelationshipInterface
{
    public function getStatusColumn();

    public function getStatusTypeValue();

    public function statuses();
}