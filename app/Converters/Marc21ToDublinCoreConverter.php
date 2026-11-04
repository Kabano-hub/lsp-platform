<?php

namespace App\Converters;

class Marc21ToDublinCoreConverter
{
    /**
     * Rekord MARC21 jest poprawny, gdy ma tytuł (245a) i ISBN (020a).
     */
    public function validateMarc21(array $data): bool
    {
        $title = trim((string) ($data['245a'] ?? ''));
        $isbn = trim((string) ($data['020a'] ?? ''));

        return $title !== '' && $isbn !== '';
    }

    /**
     * Proste mapowanie pól MARC21 na Dublin Core.
     * 245a tytuł, 100a autor, 020a ISBN, 260c rok wydania.
     */
    public function toDublinCore(array $marc21): array
    {
        return [
            'dc:title' => $marc21['245a'] ?? '',
            'dc:creator' => $marc21['100a'] ?? '',
            'dc:identifier' => $marc21['020a'] ?? '',
            'dc:date' => $marc21['260c'] ?? '',
            'dc:type' => 'Text',
        ];
    }
}
