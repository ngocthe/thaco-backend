<?php

namespace Tests;

use DMS\PHPUnitExtensions\ArraySubset\ArraySubsetAsserts;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Tests\Traits\InteractsWithBase;
use Tests\Traits\InteractsWithPermissions;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    use InteractsWithPermissions;
    use ArraySubsetAsserts;
    use InteractsWithBase;
}
