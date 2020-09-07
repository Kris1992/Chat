<?php declare(strict_types=1);

namespace App\Services\Checker;

/**
 *  Checker interface
 */
interface CheckerInterface
{   

    /**
     * check Check is given data valid
     * @param  mixed        $data   Data to check
     * @throws \Exception           Throws an \Exception when validation can't be done
     * @return bool
     */
    public function check($data): bool;

}
