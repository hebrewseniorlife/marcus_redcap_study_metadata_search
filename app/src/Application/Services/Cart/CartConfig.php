<?php

namespace Application\Services\Cart;

final class CartConfig
{
    public function __construct(
        public readonly string $sessionKey = 'cart'
    ) {}
}