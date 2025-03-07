<?php

namespace App\Modules\MasterData\Actions\Uom;

use App\Entities\Uom;
use App\Exceptions\UserException;
use App\Libraries\Language;
use App\Modules\MasterData\DTO\Uom\UomUpsertDTO;

class UomStoreAction
{
    public UomUpsertDTO $dto;
    public $uom;

    /**
     * @param UomUpsertDTO $dto
     */

    public function handle($dto)
    {
        $this->dto = $dto;

        $this->validateDataInput()
            ->makeUom();
    }

    public function validateDataInput()
    {
        $isDuplicateCode = Uom::query()
            ->where('code', $this->dto->code)
            ->first();

        if ($isDuplicateCode) {
            throw new UserException(Language::translate('Code has already been taken'));
        }
        $isDuplicateName = Uom::query()
            ->where('name', $this->dto->name)
            ->first();

        if ($isDuplicateName) {
            throw new UserException(Language::translate('Name has already been taken'));
        }

        return $this;
    }

    public function makeUom()
    {
        $this->uom = Uom::query()->create([
            'code' => $this->dto->code,
            'name' => $this->dto->name,
            'description' => $this->dto->description,
        ]);

        return $this;
    }
}
