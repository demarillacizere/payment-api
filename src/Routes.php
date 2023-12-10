<?php
/**
 * Routes.php
 * demarillac.izere
 * 20.11.2023
 */

namespace PaymentApi;

enum Routes: string
{
    case Methods = 'methods';
    case Customers = 'customers';
    case Payments = 'payments';
    case Order = 'orders';

    public function toSingular(): string
    {
        return match ($this) {
            Routes::Methods => 'method',
            Routes::Customers => 'customer',
            Routes::Payments => 'payment',
            Routes::Order => 'order',
        };
    }
}
