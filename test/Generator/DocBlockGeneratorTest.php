<?php

/**
 * @see       https://github.com/laminas/laminas-code for the canonical source repository
 * @copyright https://github.com/laminas/laminas-code/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-code/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Code\Generator;

use Laminas\Code\Generator\DocBlock\Tag;
use Laminas\Code\Generator\DocBlock\Tag\AuthorTag;
use Laminas\Code\Generator\DocBlock\Tag\LicenseTag;
use Laminas\Code\Generator\DocBlock\Tag\ParamTag;
use Laminas\Code\Generator\DocBlock\Tag\ReturnTag;
use Laminas\Code\Generator\DocBlockGenerator;
use Laminas\Code\Reflection\DocBlockReflection;
use PHPUnit\Framework\TestCase;

/**
 * @group      Laminas_Code_Generator
 * @group      Laminas_Code_Generator_Php
 */
class DocBlockGeneratorTest extends TestCase
{
    /** @var DocBlockGenerator */
    protected $docBlockGenerator;

    /** @var DocBlockGenerator */
    protected $reflectionDocBlockGenerator;

    protected function setUp(): void
    {
        $this->docBlockGenerator = $this->docBlockGenerator = new DocBlockGenerator();
        $reflectionDocBlock      = new DocBlockReflection(
            '/**
 * Short Description
 * Long Description
 * @param string $foo comment
 * @author Laminas <laminas@zend.com>
 * @license http://license The License
 * @return int
 */'
        );

        $this->reflectionDocBlockGenerator = DocBlockGenerator::fromReflection($reflectionDocBlock);
    }

    public function testCanPassTagsToConstructor()
    {
        $docBlockGenerator = new DocBlockGenerator(null, null, [
            ['name' => 'foo'],
        ]);

        $tags = $docBlockGenerator->getTags();
        self::assertCount(1, $tags);

        self::assertEquals('foo', $tags[0]->getName());
    }

    public function testShortDescriptionGetterAndSetter()
    {
        $this->docBlockGenerator->setShortDescription('Short Description');
        self::assertEquals('Short Description', $this->docBlockGenerator->getShortDescription());
    }

    public function testLongDescriptionGetterAndSetter()
    {
        $this->docBlockGenerator->setLongDescription('Long Description');
        self::assertEquals('Long Description', $this->docBlockGenerator->getLongDescription());
    }

    public function testTagGettersAndSetters()
    {
        $paramTag = new Tag\ParamTag();
        $paramTag->setDatatype('string');

        $returnTag = new Tag\ReturnTag();
        $returnTag->setDatatype('int');

        $this->docBlockGenerator->setTag(['name' => 'blah']);
        $this->docBlockGenerator->setTag($paramTag);
        $this->docBlockGenerator->setTag($returnTag);
        self::assertCount(3, $this->docBlockGenerator->getTags());

        $target = <<<EOS
/**
 * @blah
 * @param string
 * @return int
 */

EOS;

        self::assertEquals($target, $this->docBlockGenerator->generate());
    }

    public function testGenerationOfDocBlock()
    {
        $this->docBlockGenerator->setShortDescription('@var Foo this is foo bar');

        $expected = '/**' . DocBlockGenerator::LINE_FEED . ' * @var Foo this is foo bar'
            . DocBlockGenerator::LINE_FEED . ' */' . DocBlockGenerator::LINE_FEED;
        self::assertEquals($expected, $this->docBlockGenerator->generate());
    }

    public function testCreateFromArray()
    {
        $docBlock = DocBlockGenerator::fromArray([
            'shortdescription' => 'foo',
            'longdescription'  => 'bar',
            'tags'             => [
                [
                    'name'        => 'foo',
                    'description' => 'bar',
                ],
            ],
        ]);

        self::assertEquals('foo', $docBlock->getShortDescription());
        self::assertEquals('bar', $docBlock->getLongDescription());
        self::assertCount(1, $docBlock->getTags());
    }

    /**
     * @group #3753
     */
    public function testGenerateWordWrapIsEnabledByDefault()
    {
        $largeStr = '@var This is a very large string that will be wrapped if it contains more than 80 characters';
        $this->docBlockGenerator->setLongDescription($largeStr);

        $expected = '/**' . DocBlockGenerator::LINE_FEED
            . ' * @var This is a very large string that will be wrapped if it contains more than'
            . DocBlockGenerator::LINE_FEED . ' * 80 characters' . DocBlockGenerator::LINE_FEED
            . ' */' . DocBlockGenerator::LINE_FEED;
        self::assertEquals($expected, $this->docBlockGenerator->generate());
    }

    /**
     * @group #3753
     */
    public function testGenerateWithWordWrapDisabled()
    {
        $largeStr = '@var This is a very large string that will not be wrapped if it contains more than 80 characters';
        $this->docBlockGenerator->setLongDescription($largeStr);
        $this->docBlockGenerator->setWordWrap(false);

        $expected = '/**' . DocBlockGenerator::LINE_FEED
            . ' * @var This is a very large string that will not be wrapped if it contains more than'
            . ' 80 characters' . DocBlockGenerator::LINE_FEED . ' */' . DocBlockGenerator::LINE_FEED;
        self::assertEquals($expected, $this->docBlockGenerator->generate());
    }

    public function testDocBlockFromReflectionLongDescription()
    {
        self::assertEquals('Long Description', $this->reflectionDocBlockGenerator->getLongDescription());
    }

    public function testDocBlockFromReflectionShortDescription()
    {
        self::assertEquals('Short Description', $this->reflectionDocBlockGenerator->getShortDescription());
    }

    public function testDocBlockFromReflectionTagsCount()
    {
        self::assertCount(4, $this->reflectionDocBlockGenerator->getTags());
    }

    /**
     * @depends testDocBlockFromReflectionTagsCount
     */
    public function testDocBlockFromReflectionParamTag()
    {
        $tags = $this->reflectionDocBlockGenerator->getTags();
        self::assertInstanceOf(ParamTag::class, $tags[0]);
    }

    /**
     * @depends testDocBlockFromReflectionTagsCount
     */
    public function testDocBlockFromReflectionAuthorTag()
    {
        $tags = $this->reflectionDocBlockGenerator->getTags();
        self::assertInstanceOf(AuthorTag::class, $tags[1]);
    }

    /**
     * @depends testDocBlockFromReflectionTagsCount
     */
    public function testDocBlockFromReflectionLicenseTag()
    {
        $tags = $this->reflectionDocBlockGenerator->getTags();
        self::assertInstanceOf(LicenseTag::class, $tags[2]);
    }

    /**
     * @depends testDocBlockFromReflectionTagsCount
     */
    public function testDocBlockFromReflectionReturnTag()
    {
        $tags = $this->reflectionDocBlockGenerator->getTags();
        self::assertInstanceOf(ReturnTag::class, $tags[3]);
    }
}
