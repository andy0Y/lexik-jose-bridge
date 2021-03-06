<?php

declare(strict_types=1);

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2014-2019 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace SpomkyLabs\LexikJoseBundle\Checker;

use Jose\Component\Checker\HeaderChecker;
use Jose\Component\Checker\InvalidHeaderException;
use function Safe\sprintf;

/**
 * Class AlgHeaderChecker.
 */
final class AlgHeaderChecker implements HeaderChecker
{
    /**
     * @var string
     */
    private $algorithm;

    /**
     * AlgHeaderChecker constructor.
     *
     * @param string $algorithm
     */
    public function __construct(string $algorithm)
    {
        $this->algorithm = $algorithm;
    }

    /**
     * @param mixed $algorithm
     *
     * @return void
     */
    public function checkHeader($algorithm)
    {
        if (!\is_string($algorithm)) {
            throw new InvalidHeaderException('The value of the header "alg" is not valid', 'alg', $algorithm);
        }

        if ($this->algorithm !== $algorithm) {
            throw new InvalidHeaderException(sprintf('The algorithm "%s" is not known.', $algorithm), 'alg', $algorithm);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function supportedHeader(): string
    {
        return 'alg';
    }

    /**
     * {@inheritdoc}
     */
    public function protectedHeaderOnly(): bool
    {
        return true;
    }
}
