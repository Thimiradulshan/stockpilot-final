<?php

namespace App\Services;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class DocumentNumberService
{

public function next(
        string $scope,
        CarbonImmutable|string|null $date = null,
    ): string {
        $scope = trim($scope);

        if ($scope === '' || strlen($scope) > 30) {
            throw new InvalidArgumentException(
                'A document sequence scope is required.'
            );
        }

        $sequenceDate = $this->normalizeDate($date);

        return DB::transaction(function () use ($scope, $sequenceDate): string {
            $row = DB::table('document_sequences')
                ->where('scope', $scope)
                ->where('sequence_date', $sequenceDate)
                ->lockForUpdate()
                ->first();

            if ($row === null) {
                DB::table('document_sequences')->insertOrIgnore([
                    'scope' => $scope,
                    'sequence_date' => $sequenceDate,
                    'last_number' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $row = DB::table('document_sequences')
                    ->where('scope', $scope)
                    ->where('sequence_date', $sequenceDate)
                    ->lockForUpdate()
                    ->first();
            }

            if ($row === null) {
                throw new InvalidArgumentException(
                    'The document sequence could not be resolved.'
                );
            }

            $next = (int) $row->last_number + 1;

            DB::table('document_sequences')
                ->where('id', $row->id)
                ->update([
                    'last_number' => $next,
                    'updated_at' => now(),
                ]);

            return $this->format($sequenceDate, $next);
        });
    }


public function format(string $sequenceDate, int $number): string
    {
        $date = CarbonImmutable::parse($sequenceDate);

        return sprintf(
            '%s-%04d',
            $date->format('y-m-d'),
            $number,
        );
    }


private function normalizeDate(
        CarbonImmutable|string|null $date,
    ): string {
        if ($date === null) {
            return CarbonImmutable::today()->toDateString();
        }

        if (is_string($date)) {
            return CarbonImmutable::parse($date)->toDateString();
        }

        return $date->toDateString();
    }
}
