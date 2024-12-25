<?php

namespace Tests\Unit\Utils;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use ThreeLeaf\Biblioteca\Utils\UuidUtil;

/** Test {@link UuidUtil}. */
class UuidUtilTest extends TestCase
{
    /** @test {@link UuidUtil::generateDnsUuid()}. */
    public function generateDnsUuid()
    {
        $hostname = 'threeleaf.com';
        $expectedUuid = 'd4a08aa5-9661-57ab-bf61-8f28be9b1f00';

        $actualUuid = UuidUtil::generateDnsUuid($hostname);

        $this->assertEquals($expectedUuid, $actualUuid);
    }

    /** @test {@link UuidUtil::generateDnsUuid()} with special characters. */
    public function generateDnsUuidSpecialCharacters()
    {
        $hostname = 'sub-domain.threeleaf.com';
        $expectedUuid = '0f264584-d56d-5857-9d33-6dba6d0b571e';

        $actualUuid = UuidUtil::generateDnsUuid($hostname);

        $this->assertEquals($expectedUuid, $actualUuid);
    }

    /** @test {@link UuidUtil::generateDnsUuid()} case insensitive. */
    public function generateDnsUuidCaseInsensitive()
    {
        $hostname1 = 'THREELEAF.COM';
        $hostname2 = 'ThreeLeaf.com';
        $expectedUuid = 'd4a08aa5-9661-57ab-bf61-8f28be9b1f00';

        $actualUuid1 = UuidUtil::generateDnsUuid($hostname1);
        $actualUuid2 = UuidUtil::generateDnsUuid($hostname2);

        $this->assertEquals($expectedUuid, $actualUuid1);
        $this->assertEquals($expectedUuid, $actualUuid2);
    }

    /** @test {@link UuidUtil::generateDnsUuid()} with special characters. */
    public function generateDnsUuidTrailingSpaces()
    {
        $hostnameWithSpaces = '  threeleaf.com  ';
        $expectedUuid = 'd4a08aa5-9661-57ab-bf61-8f28be9b1f00';

        $actualUuid = UuidUtil::generateDnsUuid(trim($hostnameWithSpaces));

        $this->assertEquals($expectedUuid, $actualUuid);
    }

    /** @test {@link UuidUtil::generateUrlUuid()} special characters. */
    public function generateUrlUuidSpecialCharacters()
    {
        $url = 'https://threeleaf.com/blog?page=2#section1';
        $expectedUuid = '92ec7007-2f03-5da2-b442-76791a6e389c';

        $actualUuid = UuidUtil::generateUrlUuid($url);

        $this->assertEquals($expectedUuid, $actualUuid);
    }

    /** @test {@link UuidUtil::generateOidUuid()} case insensitive. */
    public function generateUrlUuidCaseInsensitive()
    {
        $url1 = 'https://ThreeLeaf.com/blog?page=2#section1';
        $url2 = 'HTTPS://THREELEAF.COM/BLOG?PAGE=2#SECTION1';
        $expectedUuid = '92ec7007-2f03-5da2-b442-76791a6e389c';

        $actualUuid1 = UuidUtil::generateUrlUuid($url1);
        $actualUuid2 = UuidUtil::generateUrlUuid($url2);

        $this->assertEquals($expectedUuid, $actualUuid1);
        $this->assertEquals($expectedUuid, $actualUuid2);
    }

    /** @test {@link UuidUtil::generateUrlUuid()} special characters. */
    public function generateUrlUuidTrailingSpaces()
    {
        $url = '   https://threeleaf.com/blog?page=2#section1  ';
        $expectedUuid = '92ec7007-2f03-5da2-b442-76791a6e389c';

        $actualUuid = UuidUtil::generateUrlUuid($url);

        $this->assertEquals($expectedUuid, $actualUuid);
    }

    /** @test {@link UuidUtil::generateOidUuid()}. */
    public function generateOidUuidSpecialCharacters()
    {
        $oid = '1.2.3.4.5';
        $expectedUuid = '8f8c57e1-8db4-5940-8ec2-e7d2c194814e';

        $actualUuid = UuidUtil::generateOidUuid($oid);

        $this->assertEquals($expectedUuid, $actualUuid);
    }

    /** @test {@link UuidUtil::generateDnsUuid()} with invalid hostname. */
    public function generateDnsUuidInvalidHostname()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid DNS hostname format: 'invalid_hostname'.");

        UuidUtil::generateDnsUuid('invalid_hostname');
    }

    /** @test {@link UuidUtil::generateUrlUuid()} with invalid URL. */
    public function generateUrlUuidInvalidUrl()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid URL format: 'invalid_url'.");

        UuidUtil::generateUrlUuid('invalid_url');
    }

    /** @test {@link UuidUtil::generateOidUuid()} with invalid OID. */
    public function generateOidUuidInvalidOid()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid OID format: 'invalid_oid'. OID must only contain numbers and periods.");

        UuidUtil::generateOidUuid('invalid_oid');
    }
}
