<?php

use App\Support\School;

if (! function_exists('school_name')) {
    /** Full school name, e.g. for page titles and letterheads. */
    function school_name(): string
    {
        return School::name();
    }
}

if (! function_exists('school_short_name')) {
    /** School name without the city, for running text. */
    function school_short_name(): string
    {
        return School::shortName();
    }
}

if (! function_exists('school_placeholders')) {
    /**
     * Replacements for translation strings that mention the school:
     * __('public.hero_desc', school_placeholders()) fills :school and :school_short.
     *
     * @return array{school: string, school_short: string}
     */
    function school_placeholders(): array
    {
        return ['school' => School::name(), 'school_short' => School::shortName()];
    }
}

if (! function_exists('school_foundation_name')) {
    function school_foundation_name(bool $withAbbreviation = true): string
    {
        return School::foundationName($withAbbreviation);
    }
}

if (! function_exists('school_address')) {
    function school_address(): string
    {
        return School::address() ?? '';
    }
}

if (! function_exists('school_phone')) {
    function school_phone(): string
    {
        return School::phone() ?? '';
    }
}

if (! function_exists('school_email')) {
    function school_email(): string
    {
        return School::email() ?? '';
    }
}

if (! function_exists('school_website')) {
    /** Website without the scheme, as printed on documents. */
    function school_website(): string
    {
        return School::websiteLabel() ?? '';
    }
}

if (! function_exists('school_npsn')) {
    function school_npsn(): string
    {
        return School::npsn() ?? '';
    }
}

if (! function_exists('school_accreditation')) {
    function school_accreditation(): string
    {
        return School::accreditation() ?? '';
    }
}

if (! function_exists('school_contact_line')) {
    /**
     * "Alamat | Telp: … | Email: …" with only the parts that are known.
     *
     * @param  array<int, string>  $parts  any of address, phone, email, website
     */
    function school_contact_line(array $parts = ['address', 'phone', 'email'], string $separator = ' | '): string
    {
        return School::contactLine($parts, $separator);
    }
}

if (! function_exists('school_principal_name')) {
    /** The principal's name, or '' until an admin enters it (there is no default). */
    function school_principal_name(): string
    {
        return School::principalName() ?? '';
    }
}

if (! function_exists('school_principal_nip')) {
    function school_principal_nip(): string
    {
        return School::principalNip() ?? '';
    }
}

if (! function_exists('signature_name')) {
    /**
     * A signer's name for a printed signature block, or the conventional
     * blank "( ……… )" to be filled by hand. Templates used to print invented
     * officials' names here.
     */
    function signature_name(?string $name): string
    {
        return filled($name) ? $name : '( ........................................ )';
    }
}

if (! function_exists('signature_nip')) {
    /** "NIP. 1968…" or a blank line to fill by hand. */
    function signature_nip(?string $nip, string $prefix = 'NIP. '): string
    {
        return $prefix.(filled($nip) ? $nip : '........................................');
    }
}
