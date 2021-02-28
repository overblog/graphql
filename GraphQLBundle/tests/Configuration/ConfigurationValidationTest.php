<?php

declare(strict_types=1);

namespace Overblog\GraphQLBundle\Tests\Configuration;

use Overblog\GraphQLBundle\Configuration\ArgumentConfiguration;
use Overblog\GraphQLBundle\Configuration\Configuration;
use Overblog\GraphQLBundle\Configuration\FieldConfiguration;
use Overblog\GraphQLBundle\Configuration\InterfaceConfiguration;
use Overblog\GraphQLBundle\Configuration\ObjectConfiguration;

class ConfigurationValidationTest extends BaseConfigurationTest
{
    /**
     * Forbidden strategy, no types with the same name
     */
    public function testDuplicateForbiddenType()
    {
        $configuration = (new Configuration())
                ->addType(self::object('DuplicatedName', ['f' => 'String']))
                ->addType(self::object('DuplicatedName', ['f' => 'String']));

        $errors = $this->getValidator()->validate($configuration);
        $this->assertCount(1, $errors);
        $error = $errors->get(0);
        $this->assertNull($error->getInvalidValue());
        $this->assertMatchesRegularExpression('/Naming collision on name "DuplicatedName", found 2 types using it/', $error->getMessage());
    }

    /**
     * Forbidden strategy, no fields with the same name
     */
    public function testDuplicateForbiddenField()
    {
        $configuration = new Configuration();
        $object = self::object('Type', [['duplicatedField', 'String'], ['duplicatedField', 'String']]);
        $configuration->addType($object);
        $errors = $this->getValidator()->validate($configuration);
        $this->assertCount(1, $errors);
        $error = $errors->get(0);
        $this->assertEquals($error->getInvalidValue(), $object);
        $this->assertMatchesRegularExpression('/Naming collision on name "duplicatedField", found 2 fields using it/', $error->getMessage());
    }

    /**
     * Forbidden strategy, no input fields with the same name
     */
    public function testDuplicateForbiddenInputField()
    {
        $configuration = new Configuration();
        $input = self::input('Input', [['duplicatedField', 'String'], ['duplicatedField', 'String']]);
        $configuration->addType($input);
        $errors = $this->getValidator()->validate($configuration);
        $this->assertCount(1, $errors);
        $error = $errors->get(0);
        $this->assertEquals($error->getInvalidValue(), $input);
        $this->assertMatchesRegularExpression('/Naming collision on name "duplicatedField", found 2 fields using it/', $error->getMessage());
    }

    /**
     * Forbidden strategy, no field arguments with the same name
     */
    public function testDuplicateForbiddenArg()
    {
        $configuration = new Configuration();
        $object = new ObjectConfiguration('Type');
        $field = (new FieldConfiguration('field', 'String'))
            ->addArgument(new ArgumentConfiguration('duplicatedArgument', 'String'))
            ->addArgument(new ArgumentConfiguration('duplicatedArgument', 'Int'));

        $object->addField($field);
        $configuration->addType($object);
        $errors = $this->getValidator()->validate($configuration);
        $this->assertCount(1, $errors);
        $error = $errors->get(0);
        $this->assertEquals($error->getInvalidValue(), $field);
        $this->assertMatchesRegularExpression('/Naming collision on name "duplicatedArgument", found 2 arguments using it/', $error->getMessage());
    }

    /**
     * Override Same Type strategy, override same type ok
     */
    public function testDuplicateStrategyOverrideWithMatchingTypes()
    {
        $configuration = (new Configuration(Configuration::DUPLICATE_STRATEGY_OVERRIDE_SAME_TYPE))
                ->addType(self::object('DuplicatedName', ['f' => 'String']))
                ->addType(self::object('DuplicatedName', ['f' => 'String'])->setDescription('Second'));

        $errors = $this->getValidator()->validate($configuration);
        $this->assertCount(0, $errors);
        $this->assertCount(1, $configuration->getTypes());
        $this->assertEquals('Second', $configuration->getType('DuplicatedName')->getDescription());
    }

    /**
     * Override Same Type strategy, not override different types
     */
    public function testDuplicateStrategyOverrideWithUnmatchingTypes()
    {
        $configuration = (new Configuration(Configuration::DUPLICATE_STRATEGY_OVERRIDE_SAME_TYPE))
                ->addType(self::object('DuplicatedName', ['f' => 'String']))
                ->addType(self::input('DuplicatedName', ['f' => 'String']));

        $errors = $this->getValidator()->validate($configuration);
        $this->assertCount(1, $errors);
        $error = $errors->get(0);
        $this->assertEquals($error->getInvalidValue(), null);
        $this->assertMatchesRegularExpression('/Naming collision on name "DuplicatedName", found 2 types using it/', $error->getMessage());
    }

    /**
     * Input fields can't have object types
     */
    public function testInvalidInputFieldType()
    {
        $object = self::object('Type', ['f' => 'String']);
        $input = self::input('Input', ['field1' => 'Type']);
        $configuration = (new Configuration())
            ->addType($object)
            ->addType($input);

        $errors = $this->getValidator()->validate($configuration);
        $this->assertCount(1, $errors);
        $error = $errors->get(0);
        $this->assertEquals($error->getInvalidValue(), $input->getField('field1'));
        $this->assertEquals('Incompatible type "Type" (object). Accepted types for input fields are : scalar, enum, input', $error->getMessage());
    }

    /**
     * Object fields can't have input type
     */
    public function testInvalidObjectFieldType()
    {
        $object = self::object('Type', ['badField' => 'Input']);
        $input = self::input('Input', ['f' => 'String']);
        $configuration = (new Configuration())
            ->addType($object)
            ->addType($input);

        $errors = $this->getValidator()->validate($configuration);
        $this->assertCount(1, $errors);
        $error = $errors->get(0);
        $this->assertEquals($error->getInvalidValue(), $object->getField('badField'));
        $this->assertEquals('Incompatible type "Input" (input). Accepted types for object fields are : scalar, object, interface, union, enum', $error->getMessage());
    }

    /**
     * Object fields must have fields
     */
    public function testMissingObjectFields()
    {
        $object = self::object('MyType');
        $interface = new InterfaceConfiguration('MyInterface');
        $configuration = (new Configuration())
            ->addType($object)
            ->addType($interface);

        $errors = $this->getValidator()->validate($configuration);
        $this->assertCount(2, $errors);

        $error = $errors->get(0);
        $this->assertEquals($error->getInvalidValue(), $object);
        $this->assertEquals('The object "MyType" has no field', $error->getMessage());

        $error = $errors->get(1);
        $this->assertEquals($error->getInvalidValue(), $interface);
        $this->assertEquals('The interface "MyInterface" has no field', $error->getMessage());
    }

    /**
     * Names must match graphql spec
     */
    public function testInvalidNames()
    {
        $configuration = new Configuration();
        $object = self::object('Invalid+Name', ['f' => 'String']);
        $input = self::input('Invli*Input', ['f' => 'String']);
        $object2 = self::object('GoodName', ['f' => 'String']);
        $badField = new FieldConfiguration('++BadFieldName', 'String');
        $object2->addField($badField);

        $object3 = new ObjectConfiguration('GoodObject');
        $field = new FieldConfiguration('GoodField', 'String');
        $badArgument = new ArgumentConfiguration('1BadArgument', 'Int');
        $field->addArgument($badArgument);
        $object3->addField($field);

        $configuration
            ->addType($object)
            ->addType($input)
            ->addType($object2)
            ->addType($object3);

        $errors = $this->getValidator()->validate($configuration);
        $this->assertCount(4, $errors);

        $errorObject = $errors->get(0);
        $this->assertEquals($errorObject->getInvalidValue(), $object);
        $this->assertEquals('The name "Invalid+Name" is not valid. Allowed characters are letters, numbers and _.', $errorObject->getMessage());

        $errorInput = $errors->get(1);
        $this->assertEquals($errorInput->getInvalidValue(), $input);
        $this->assertEquals('The name "Invli*Input" is not valid. Allowed characters are letters, numbers and _.', $errorInput->getMessage());

        $errorObject2 = $errors->get(2);
        $this->assertEquals($errorObject2->getInvalidValue(), $badField);
        $this->assertEquals('The name "++BadFieldName" is not valid. Allowed characters are letters, numbers and _.', $errorObject2->getMessage());

        $errorObject3 = $errors->get(3);
        $this->assertEquals($errorObject3->getInvalidValue(), $badArgument);
        $this->assertEquals('The name "1BadArgument" is not valid. Allowed characters are letters, numbers and _.', $errorObject3->getMessage());
    }
}
