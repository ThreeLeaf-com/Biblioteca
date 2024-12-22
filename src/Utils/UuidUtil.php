<?php

namespace ThreeLeaf\Biblioteca\Utils;

use Symfony\Component\Uid\Uuid;

/**
 * UUID helper functions.
 *
 * @see https://datatracker.ietf.org/doc/html/rfc9562 for details about UUIDs.
 */
class UuidUtil
{

    /**
     * Generate a deterministic UUID using the DNS namespace.
     *
     * @param string $hostname The hostname to convert.
     *
     * @return string The deterministic UUID for the given hostname.
     */
    static function generateDnsUuid(string $hostname): string
    {
        /* Generate a deterministic UUIDv5: */
        $dnsUuid = Uuid::v5(
            Uuid::fromString(Uuid::NAMESPACE_DNS),
            $hostname
        );

        return (string)$dnsUuid;
    }

    /**
     * Generate a deterministic UUID using the URL namespace.
     *
     * @param string $url The URL to convert.
     *
     * @return string The deterministic UUID for the given URL.
     */
    static function generateUrlUuid(string $url): string
    {
        /* Generate a deterministic UUIDv5: */
        $urlUuid = Uuid::v5(
            Uuid::fromString(Uuid::NAMESPACE_URL),
            $url
        );

        return (string)$urlUuid;
    }

    /**
     * Generate a deterministic UUID using the OID namespace.
     *
     * @param string $oid The OID string to convert.
     *
     * @return string The deterministic UUID for the given OID.
     */
    static function generateOidUuid(string $oid): string
    {
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
