<?php

namespace ThreeLeaf\Biblioteca\Utils;

use InvalidArgumentException;
use Symfony\Component\Uid\Uuid;

/**
 * UUID helper functions.
 *
 * @see https://datatracker.ietf.org/doc/html/rfc9562 for details about UUIDs.
 */
class UuidUtil
{

    /**
     * Generate a deterministic UUID using the Domain Name Service (DNS) namespace.
     *
     * @param string $hostname The DNS hostname to convert.
     *
     * @return string The deterministic UUID for the given hostname.
     * @throws InvalidArgumentException If the hostname format is invalid.
     * @see https://datatracker.ietf.org/doc/html/rfc1035
     */
    static function generateDnsUuid(string $hostname): string
    {
        $hostname = trim(strtolower($hostname));
        if (!filter_var($hostname, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME)) {
            throw new InvalidArgumentException("Invalid DNS hostname format: '$hostname'.");
        }

        $dnsUuid = Uuid::v5(
            Uuid::fromString(Uuid::NAMESPACE_DNS),
            $hostname
        );

        return (string)$dnsUuid;
    }

    /**
     * Generate a deterministic UUID using the Uniform Resource Locator (URL) namespace.
     *
     * @param string $url The URL to convert.
     *
     * @return string The deterministic UUID for the given URL.
     * @throws InvalidArgumentException If the URL format is invalid.
     */
    static function generateUrlUuid(string $url): string
    {
        $url = trim(strtolower($url));
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new InvalidArgumentException("Invalid URL format: '$url'.");
        }

        $urlUuid = Uuid::v5(
            Uuid::fromString(Uuid::NAMESPACE_URL),
            $url
        );

        return (string)$urlUuid;
    }

    /**
     * Generate a deterministic UUID using the Object Identifier namespace.
     *
     * @param string $oid The OID string to convert.
     *
     * @return string The deterministic UUID for the given OID.
     * @throws InvalidArgumentException If the OID format is invalid.
     */
    static function generateOidUuid(string $oid): string
    {
        /* Validate the OID format: Only numbers and periods allowed */
        if (!preg_match('/^\d+(\.\d+)*$/', $oid)) {
            throw new InvalidArgumentException("Invalid OID format: '$oid'. OID must only contain numbers and periods.");
        }

        /* Generate a deterministic UUIDv5: */
        $oidUuid = Uuid::v5(
            Uuid::fromString(Uuid::NAMESPACE_OID),
            $oid
        );

        return (string)$oidUuid;
    }

    /**
     * Generate a UUIDv5 based on a Distinguished Name (DN) using the X.500 namespace.
     *
     * @param string $distinguishedName The distinguished name to convert.
     *
     * @return string The deterministic UUID for the given distinguished name.
     * @see https://zavyn.blogspot.com/2024/12/creating-deterministic-uuids.html for details about using x.500 distinguished
     * names to generate deterministic UUIDs.
     */
    static function generateX500Uuid(string $distinguishedName): string
    {
        /* Generate a deterministic UUIDv5: */
        $uuid = Uuid::v5(
            Uuid::fromString(Uuid::NAMESPACE_X500),
            $distinguishedName
        );

        return (string)$uuid;
    }
}
