<?php

namespace Application\Service\Cart;

final class CartConfig
{
    public function __construct(
        public readonly string $sessionKey = 'cart'
    ) {}
}