<?php

use Carbon\Carbon;
use CoderAtHeart\ObjectModel\Tests\ENUMs\Country;
use CoderAtHeart\ObjectModel\Tests\Models\Address;
use CoderAtHeart\ObjectModel\Tests\Models\AddressUS;
use CoderAtHeart\ObjectModel\Tests\Models\Person;
use CoderAtHeart\ObjectModel\Tests\Models\Phone;
use CoderAtHeart\ObjectModel\Tests\Models\PhoneNumbers;

$sunil = Person::createFrom(array: [
    'name'  => 'Sunil',
    'age'   => 50,
    'email' => 'spam.me.to.death@coderatheart.com',
]);

test('properties are as expected', function () use ($sunil) {
    expect($sunil->name)->toBeString()->toBe('Sunil');
    expect($sunil->age)->toBeInt()->toBe(50);
    expect($sunil->email)->toBeString()->toBe('spam.me.to.death@coderatheart.com');
    expect($sunil->home)->toBeInstanceOf(Address::class);
    expect($sunil->home->address_1)->toBeString()->toBeEmpty();
    expect($sunil->home->address_2)->toBeString()->toBeEmpty();
    expect($sunil->home->city)->toBeString()->toBeEmpty();
    expect($sunil->home->postcode)->toBeString()->toBeEmpty();
    expect($sunil->home->country_code)->toBe(Country::GB);
    expect($sunil->business)->toBeInstanceOf(Address::class);
    expect($sunil->business->address_1)->toBeString()->toBeEmpty();
    expect($sunil->business->address_2)->toBeString()->toBeEmpty();
    expect($sunil->business->city)->toBeString()->toBeEmpty();
    expect($sunil->business->postcode)->toBeString()->toBeEmpty();
    expect($sunil->business->country_code)->toBe(Country::GB);
    expect($sunil->phone_numbers)->toBeInstanceOf(PhoneNumbers::class)->toBeEmpty();
    expect($sunil->birthday->format('Y-m-d H:i'))->toBe(Carbon::now()->format('Y-m-d H:i'));
    expect($sunil->alarm->format('g:i a'))->toBe('8:00 am');
    expect($sunil->subscribed)->toBeBool()->toBe(false);
    expect($sunil->friends)->toBeArray()->toBeEmpty();
});

test('we can set and change basic properties', function () use ($sunil) {
    $sunil->name  = 'Coder';
    $sunil->age   = 20;
    $sunil->email = 'contact@coderatheart.com';
    expect($sunil->name)->toBeString()->toBe('Coder');
    expect($sunil->age)->toBeInt()->toBe(20);
    expect($sunil->email)->toBeString()->toBe('contact@coderatheart.com');
});

test('we can set and change date properties', function () use ($sunil) {
    $sunil->birthday = '1972-03-29';
    expect($sunil->birthday->format('jS F Y'))->toBe('29th March 1972');
    $sunil->birthday->addYears(10);
    expect($sunil->birthday->format('jS F Y'))->toBe('29th March 1982');
});

test('we can set and change time properties', function () use ($sunil) {
    $sunil->alarm = '08:00:00';
    expect($sunil->alarm->format('g:i a'))->toBe('8:00 am');
    $sunil->alarm->addHours(2);
    expect($sunil->alarm->format('g:i A'))->toBe('10:00 AM');
});

test('we can the object Model properties', function () use ($sunil) {
    $sunil->home->city         = 'London';
    $sunil->business->postcode = 'EC1 7HP';
    expect($sunil->home->city)->toBeString()->toBe('London');
    expect($sunil->business->postcode)->toBeString()->toBe('EC1 7HP');
});

test('we can work with normal arrays', function () use ($sunil) {
    $sunil->friends = ['Bob', 'Fred', 'Isabel'];
    expect($sunil->friends)->toBeArray()->toHaveCount(3);
    $sunil->friends[] = 'Alice';
    expect($sunil->friends)->toHaveCount(4);
});

test('we can create an object arrays', closure: function () use ($sunil) {
    expect(count($sunil->phone_numbers))->toBe(0);

    $homePhone                    = new Phone();
    $homePhone->label             = 'home';
    $homePhone->number            = '01234 567890';
    $sunil->phone_numbers['home'] = $homePhone;
    expect($sunil->phone_numbers)->toHaveCount(1);
    $sunil->phone_numbers['business'] = Phone::createFrom(array: [
        'label'  => 'mobile',
        'number' => '07123 987654',
    ]);
    expect($sunil->phone_numbers)->toHaveCount(2);

    $json    = $sunil->toJson();
    $person2 = Person::createFrom(json: $json);
    expect($person2->toJson())->toBe($json);
});

test('that we can create a new object from json', function () {
    $json  = json_encode([
        'name'          => 'Coder At Heart',
        'age'           => 30,
        'email'         => 'coderatheart@gmail.com',
        'home'          => [
            'address_1' => 'Some Street',
            'address_2' => 'Some Area',
            'city'      => 'Some City',
            'postcode'  => 'AB12 3CD',
        ],
        'business'      => [
            'address_1' => 'Some Business',
            'address_2' => 'Some Location',
            'city'      => 'Some City',
            'postcode'  => 'AB99 9DC',
        ],
        'phone_numbers' => [
            [
                'label'  => 'home',
                'number' => '01234 67890',
            ],
            [
                'label'  => 'mobile',
                'number' => '09999 123456',
            ],
        ],
        'birthday'      => '1990-01-01',
        'alarm'         => '10:00:00',
    ]);
    $coder = Person::createFrom(json: $json);
    expect($coder->phone_numbers)->toHaveCount(2);
    $json  = $coder->toJson();
    $clone = Person::createFrom(json: $json);
    expect($json)->toBe($clone->toJson());
});

test('we can create an object with custom properties', function () {
    $usAddress = AddressUS::createFrom(array: [
        'address_1' => 'Some Street',
        'address_2' => 'Some Area',
        'city'      => 'LA',
        'zip'       => '90210',
    ]);

    expect($usAddress->zip)->toBe('90210');
});
