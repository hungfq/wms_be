<?php

namespace App\Modules\MasterData\Actions\Uom;

use App\Entities\Uom;
use App\Exceptions\UserException;
use App\Libraries\Language;
use App\Modules\MasterData\DTO\Uom\UomUpsertDTO;

class UomUpdateAction
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
        $this->uom = Uom::query()->find($this->dto->id);
        if (!$this->uom) {
            throw new UserException(Language::translate('Uom not found'));
        }

        $isDuplicateCode = Uom::query()
            ->where('id', '<>', $this->dto->id)
            ->where('code', $this->dto->code)
            ->first();

        if ($isDuplicateCode) {
            throw new UserException(Language::translate('Code has already been taken'));
        }
        $isDuplicateName = Uom::query()
            ->where('id', '<>', $this->dto->id)
            ->where('name', $this->dto->name)
            ->first();

        if ($isDuplicateName) {
            throw new UserException(Language::translate('Name has already been taken'));
        }

        return $this;
    }

    public function makeUom()
    {
        $this->uom->update([
            'code' => $this->dto->code,
            'name' => $this->dto->name,
            'description' => $this->dto->description,
        ]);

        return $this;
    }
}
