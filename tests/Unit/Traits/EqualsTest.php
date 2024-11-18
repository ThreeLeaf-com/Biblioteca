<?php

namespace Tests\Unit\Traits;

use Illuminate\Database\Eloquent\Model;
use PHPUnit\Framework\TestCase;
use ThreeLeaf\Biblioteca\Traits\Equals;

/** Test {@link Equals}. */
class EqualsTest extends TestCase
{
    /** @test {@link Equals::equals()}. */
    public function equals()
    {
        $model1 = new Model1();

        $model2 = new Model1();

        $this->assertTrue($model1->equals($model2));
    }

    /** @test {@link Equals::equals()} when models not equal. */
    public function equalsFalse()
    {
        $model1 = new Model1([
            'attribute1' => 'value1',
        ]);

        $model2 = new Model1([
            'attribute1' => 'value2',
        ]);

        $this->assertFalse($model1->equals($model2));
    }

    /** @test {@link Equals::equals()} test against specific values only. */
    public function equalsSpecificAttributeOnly()
    {
        $model1 = new Model1([
            'attribute1' => 'value1',
            'attribute2' => 'value3',
        ]);

        $model2 = new Model1([
            'attribute1' => 'value2',
            'attribute2' => 'value3',
        ]);

        $this->assertTrue($model1->equals($model2, ['attribute2']));
    }

    /** @test {@link Equals::equals()} test against specific values fails. */
    public function equalsSpecificAttributeFalse()
    {
        $model1 = new Model1([
            'attribute1' => 'value1',
            'attribute2' => 'value3',
        ]);

        $model2 = new Model1([
            'attribute1' => 'value2',
            'attribute2' => 'value4',
        ]);

        $this->assertFalse($model1->equals($model2, ['attribute2']));
    }

    /** @test {@link Equals::equals()} when keys not equal. */
    public function equalsKeyFalse()
    {
        $model1 = new Model1([
            'id' => 1,
        ]);

        $model2 = new Model1([
            'id' => 2,
        ]);

        $this->assertTrue($model1->equals($model2));
    }

    /** @test {@link Equals::equals()} ignoring keys. */
    public function equalsNotKey()
    {
        $model1 = new Model1([
            'id' => 1,
        ]);

        $model2 = new Model1([
            'id' => 2,
        ]);

        $this->assertTrue($model1->equals($model2, null, false));
    }

    /** @test {@link Equals::equals()} for different models. */
    public function equalsDifferentModels()
    {
        $model1 = new Model1();

        $model2 = new Model2();

        $this->assertFalse($model1->equals($model2));
    }

}

/** Test model 1 */
class Model1 extends Model
{
    use Equals;

    protected $fillable = ['attribute1', 'attribute2'];

    protected $primaryKey = 'id';
}

/** Test model 2 */
class Model2 extends Model
{
    use Equals;

    protected $fillable = ['attribute1', 'attribute2'];

    protected $primaryKey = 'id';
}
