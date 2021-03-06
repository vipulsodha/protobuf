<?php

require_once('test_base.php');
require_once('test_util.php');

use Google\Protobuf\RepeatedField;
use Google\Protobuf\GPBType;
use Foo\TestEnum;
use Foo\TestMessage;
use Foo\TestMessage\Sub;
use Foo\TestPackedMessage;
use Foo\TestRandomFieldOrder;
use Foo\TestUnpackedMessage;
use Google\Protobuf\DoubleValue;
use Google\Protobuf\FloatValue;
use Google\Protobuf\Int32Value;
use Google\Protobuf\UInt32Value;
use Google\Protobuf\Int64Value;
use Google\Protobuf\UInt64Value;
use Google\Protobuf\BoolValue;
use Google\Protobuf\StringValue;
use Google\Protobuf\BytesValue;
use Google\Protobuf\Value;
use Google\Protobuf\ListValue;
use Google\Protobuf\Struct;

class EncodeDecodeTest extends TestBase
{
    public function testDecodeJsonSimple()
    {
        $m = new TestMessage();
        $m->mergeFromJsonString("{\"optionalInt32\":1}");
    }

    public function testDecodeTopLevelBoolValue()
    {
        $m = new BoolValue();

        $m->mergeFromJsonString("true");
        $this->assertEquals(true, $m->getValue());

        $m->mergeFromJsonString("false");
        $this->assertEquals(false, $m->getValue());
    }

    public function testEncodeTopLevelBoolValue()
    {
        $m = new BoolValue();
        $m->setValue(true);
        $this->assertSame("true", $m->serializeToJsonString());
    }

    public function testDecodeTopLevelDoubleValue()
    {
        $m = new DoubleValue();
        $m->mergeFromJsonString("1.5");
        $this->assertEquals(1.5, $m->getValue());
    }

    public function testEncodeTopLevelDoubleValue()
    {
        $m = new DoubleValue();
        $m->setValue(1.5);
        $this->assertSame("1.5", $m->serializeToJsonString());
    }

    public function testDecodeTopLevelFloatValue()
    {
        $m = new FloatValue();
        $m->mergeFromJsonString("1.5");
        $this->assertEquals(1.5, $m->getValue());
    }

    public function testEncodeTopLevelFloatValue()
    {
        $m = new FloatValue();
        $m->setValue(1.5);
        $this->assertSame("1.5", $m->serializeToJsonString());
    }

    public function testDecodeTopLevelInt32Value()
    {
        $m = new Int32Value();
        $m->mergeFromJsonString("1");
        $this->assertEquals(1, $m->getValue());
    }

    public function testEncodeTopLevelInt32Value()
    {
        $m = new Int32Value();
        $m->setValue(1);
        $this->assertSame("1", $m->serializeToJsonString());
    }

    public function testDecodeTopLevelUInt32Value()
    {
        $m = new UInt32Value();
        $m->mergeFromJsonString("1");
        $this->assertEquals(1, $m->getValue());
    }

    public function testEncodeTopLevelUInt32Value()
    {
        $m = new UInt32Value();
        $m->setValue(1);
        $this->assertSame("1", $m->serializeToJsonString());
    }

    public function testDecodeTopLevelInt64Value()
    {
        $m = new Int64Value();
        $m->mergeFromJsonString("1");
        $this->assertEquals(1, $m->getValue());
    }

    # public function testEncodeTopLevelInt64Value()
    # {
    #     $m = new Int64Value();
    #     $m->setValue(1);
    #     $this->assertSame("\"1\"", $m->serializeToJsonString());
    # }

    public function testDecodeTopLevelUInt64Value()
    {
        $m = new UInt64Value();
        $m->mergeFromJsonString("1");
        $this->assertEquals(1, $m->getValue());
    }

    # public function testEncodeTopLevelUInt64Value()
    # {
    #     $m = new UInt64Value();
    #     $m->setValue(1);
    #     $this->assertSame("\"1\"", $m->serializeToJsonString());
    # }

    public function testDecodeTopLevelStringValue()
    {
        $m = new StringValue();
        $m->mergeFromJsonString("\"a\"");
        $this->assertSame("a", $m->getValue());
    }

    public function testEncodeTopLevelStringValue()
    {
        $m = new StringValue();
        $m->setValue("a");
        $this->assertSame("\"a\"", $m->serializeToJsonString());
    }

    public function testDecodeTopLevelBytesValue()
    {
        $m = new BytesValue();
        $m->mergeFromJsonString("\"YQ==\"");
        $this->assertSame("a", $m->getValue());
    }

    public function testEncodeTopLevelBytesValue()
    {
        $m = new BytesValue();
        $m->setValue("a");
        $this->assertSame("\"YQ==\"", $m->serializeToJsonString());
    }

    public function testEncode()
    {
        $from = new TestMessage();
        $this->expectEmptyFields($from);
        $this->setFields($from);
        $this->expectFields($from);

        $data = $from->serializeToString();
        $this->assertSame(bin2hex(TestUtil::getGoldenTestMessage()),
                          bin2hex($data));
    }

    public function testDecode()
    {
        $to = new TestMessage();
        $to->mergeFromString(TestUtil::getGoldenTestMessage());
        $this->expectFields($to);
    }

    public function testEncodeDecode()
    {
        $from = new TestMessage();
        $this->expectEmptyFields($from);
        $this->setFields($from);
        $this->expectFields($from);

        $data = $from->serializeToString();

        $to = new TestMessage();
        $to->mergeFromString($data);
        $this->expectFields($to);
    }

    public function testEncodeDecodeEmpty()
    {
        $from = new TestMessage();
        $this->expectEmptyFields($from);

        $data = $from->serializeToString();

        $to = new TestMessage();
        $to->mergeFromString($data);
        $this->expectEmptyFields($to);
    }

    public function testEncodeDecodeOneof()
    {
        $m = new TestMessage();

        $m->setOneofInt32(1);
        $data = $m->serializeToString();
        $n = new TestMessage();
        $n->mergeFromString($data);
        $this->assertSame(1, $n->getOneofInt32());

        $m->setOneofFloat(2.0);
        $data = $m->serializeToString();
        $n = new TestMessage();
        $n->mergeFromString($data);
        $this->assertSame(2.0, $n->getOneofFloat());

        $m->setOneofString('abc');
        $data = $m->serializeToString();
        $n = new TestMessage();
        $n->mergeFromString($data);
        $this->assertSame('abc', $n->getOneofString());

        $sub_m = new Sub();
        $sub_m->setA(1);
        $m->setOneofMessage($sub_m);
        $data = $m->serializeToString();
        $n = new TestMessage();
        $n->mergeFromString($data);
        $this->assertSame(1, $n->getOneofMessage()->getA());

        // Encode default value
        $m->setOneofEnum(TestEnum::ZERO);
        $data = $m->serializeToString();
        $n = new TestMessage();
        $n->mergeFromString($data);
        $this->assertSame("oneof_enum", $n->getMyOneof());
        $this->assertSame(TestEnum::ZERO, $n->getOneofEnum());

        $m->setOneofString("");
        $data = $m->serializeToString();
        $n = new TestMessage();
        $n->mergeFromString($data);
        $this->assertSame("oneof_string", $n->getMyOneof());
        $this->assertSame("", $n->getOneofString());

        $sub_m = new Sub();
        $m->setOneofMessage($sub_m);
        $data = $m->serializeToString();
        $n = new TestMessage();
        $n->mergeFromString($data);
        $this->assertSame("oneof_message", $n->getMyOneof());
        $this->assertFalse(is_null($n->getOneofMessage()));

    }

    public function testPackedEncode()
    {
        $from = new TestPackedMessage();
        TestUtil::setTestPackedMessage($from);
        $this->assertSame(TestUtil::getGoldenTestPackedMessage(),
                          $from->serializeToString());
    }

    public function testPackedDecodePacked()
    {
        $to = new TestPackedMessage();
        $to->mergeFromString(TestUtil::getGoldenTestPackedMessage());
        TestUtil::assertTestPackedMessage($to);
    }

    public function testPackedDecodeUnpacked()
    {
        $to = new TestPackedMessage();
        $to->mergeFromString(TestUtil::getGoldenTestUnpackedMessage());
        TestUtil::assertTestPackedMessage($to);
    }

    public function testUnpackedEncode()
    {
        $from = new TestUnpackedMessage();
        TestUtil::setTestPackedMessage($from);
        $this->assertSame(TestUtil::getGoldenTestUnpackedMessage(),
                          $from->serializeToString());
    }

    public function testUnpackedDecodePacked()
    {
        $to = new TestUnpackedMessage();
        $to->mergeFromString(TestUtil::getGoldenTestPackedMessage());
        TestUtil::assertTestPackedMessage($to);
    }

    public function testUnpackedDecodeUnpacked()
    {
        $to = new TestUnpackedMessage();
        $to->mergeFromString(TestUtil::getGoldenTestUnpackedMessage());
        TestUtil::assertTestPackedMessage($to);
    }

    public function testDecodeInt64()
    {
        // Read 64 testing
        $testVals = array(
            '10'                 => '100a',
            '100'                => '1064',
            '800'                => '10a006',
            '6400'               => '108032',
            '70400'              => '1080a604',
            '774400'             => '1080a22f',
            '9292800'            => '108098b704',
            '74342400'           => '1080c0b923',
            '743424000'          => '108080bfe202',
            '8177664000'         => '108080b5bb1e',
            '65421312000'        => '108080a8dbf301',
            '785055744000'       => '108080e0c7ec16',
            '9420668928000'      => '10808080dd969202',
            '103627358208000'    => '10808080fff9c717',
            '1139900940288000'   => '10808080f5bd978302',
            '13678811283456000'  => '10808080fce699a618',
            '109430490267648000' => '10808080e0b7ceb1c201',
            '984874412408832000' => '10808080e0f5c1bed50d',
        );

        $msg = new TestMessage();
        foreach ($testVals as $original => $encoded) {
            $msg->setOptionalInt64($original);
            $data = $msg->serializeToString();
            $this->assertSame($encoded, bin2hex($data));
            $msg->setOptionalInt64(0);
            $msg->mergeFromString($data);
            $this->assertEquals($original, $msg->getOptionalInt64());
        }
    }

    public function testDecodeToExistingMessage()
    {
        $m1 = new TestMessage();
        $this->setFields($m1);
        $this->expectFields($m1);

        $m2 = new TestMessage();
        $this->setFields2($m2);
        $data = $m2->serializeToString();

        $m1->mergeFromString($data);
        $this->expectFieldsMerged($m1);
    }

    public function testDecodeFieldNonExist()
    {
        $data = hex2bin('c80501');
        $m = new TestMessage();
        $m->mergeFromString($data);
    }

    public function testEncodeNegativeInt32()
    {
        $m = new TestMessage();
        $m->setOptionalInt32(-1);
        $data = $m->serializeToString();
        $this->assertSame("08ffffffffffffffffff01", bin2hex($data));
    }

    public function testDecodeNegativeInt32()
    {
        $m = new TestMessage();
        $this->assertEquals(0, $m->getOptionalInt32());
        $m->mergeFromString(hex2bin("08ffffffffffffffffff01"));
        $this->assertEquals(-1, $m->getOptionalInt32());

        $m = new TestMessage();
        $this->assertEquals(0, $m->getOptionalInt32());
        $m->mergeFromString(hex2bin("08ffffffff0f"));
        $this->assertEquals(-1, $m->getOptionalInt32());
    }

    public function testRandomFieldOrder()
    {
        $m = new TestRandomFieldOrder();
        $data = $m->serializeToString();
        $this->assertSame("", $data);
    }

    /**
     * @expectedException Exception
     */
    public function testDecodeInvalidInt32()
    {
        $m = new TestMessage();
        $m->mergeFromString(hex2bin('08'));
    }

    /**
     * @expectedException Exception
     */
    public function testDecodeInvalidSubMessage()
    {
        $m = new TestMessage();
        $m->mergeFromString(hex2bin('9A010108'));
    }

    /**
     * @expectedException Exception
     */
    public function testDecodeInvalidInt64()
    {
        $m = new TestMessage();
        $m->mergeFromString(hex2bin('10'));
    }

    /**
     * @expectedException Exception
     */
    public function testDecodeInvalidUInt32()
    {
        $m = new TestMessage();
        $m->mergeFromString(hex2bin('18'));
    }

    /**
     * @expectedException Exception
     */
    public function testDecodeInvalidUInt64()
    {
        $m = new TestMessage();
        $m->mergeFromString(hex2bin('20'));
    }

    /**
     * @expectedException Exception
     */
    public function testDecodeInvalidSInt32()
    {
        $m = new TestMessage();
        $m->mergeFromString(hex2bin('28'));
    }

    /**
     * @expectedException Exception
     */
    public function testDecodeInvalidSInt64()
    {
        $m = new TestMessage();
        $m->mergeFromString(hex2bin('30'));
    }

    /**
     * @expectedException Exception
     */
    public function testDecodeInvalidFixed32()
    {
        $m = new TestMessage();
        $m->mergeFromString(hex2bin('3D'));
    }

    /**
     * @expectedException Exception
     */
    public function testDecodeInvalidFixed64()
    {
        $m = new TestMessage();
        $m->mergeFromString(hex2bin('41'));
    }

    /**
     * @expectedException Exception
     */
    public function testDecodeInvalidSFixed32()
    {
        $m = new TestMessage();
        $m->mergeFromString(hex2bin('4D'));
    }

    /**
     * @expectedException Exception
     */
    public function testDecodeInvalidSFixed64()
    {
        $m = new TestMessage();
        $m->mergeFromString(hex2bin('51'));
    }

    /**
     * @expectedException Exception
     */
    public function testDecodeInvalidFloat()
    {
        $m = new TestMessage();
        $m->mergeFromString(hex2bin('5D'));
    }

    /**
     * @expectedException Exception
     */
    public function testDecodeInvalidDouble()
    {
        $m = new TestMessage();
        $m->mergeFromString(hex2bin('61'));
    }

    /**
     * @expectedException Exception
     */
    public function testDecodeInvalidBool()
    {
        $m = new TestMessage();
        $m->mergeFromString(hex2bin('68'));
    }

    /**
     * @expectedException Exception
     */
    public function testDecodeInvalidStringLengthMiss()
    {
        $m = new TestMessage();
        $m->mergeFromString(hex2bin('72'));
    }

    /**
     * @expectedException Exception
     */
    public function testDecodeInvalidStringDataMiss()
    {
        $m = new TestMessage();
        $m->mergeFromString(hex2bin('7201'));
    }

    /**
     * @expectedException Exception
     */
    public function testDecodeInvalidBytesLengthMiss()
    {
        $m = new TestMessage();
        $m->mergeFromString(hex2bin('7A'));
    }

    /**
     * @expectedException Exception
     */
    public function testDecodeInvalidBytesDataMiss()
    {
        $m = new TestMessage();
        $m->mergeFromString(hex2bin('7A01'));
    }

    /**
     * @expectedException Exception
     */
    public function testDecodeInvalidEnum()
    {
        $m = new TestMessage();
        $m->mergeFromString(hex2bin('8001'));
    }

    /**
     * @expectedException Exception
     */
    public function testDecodeInvalidMessageLengthMiss()
    {
        $m = new TestMessage();
        $m->mergeFromString(hex2bin('8A01'));
    }

    /**
     * @expectedException Exception
     */
    public function testDecodeInvalidMessageDataMiss()
    {
        $m = new TestMessage();
        $m->mergeFromString(hex2bin('8A0101'));
    }

    /**
     * @expectedException Exception
     */
    public function testDecodeInvalidPackedMessageLength()
    {
        $m = new TestPackedMessage();
        $m->mergeFromString(hex2bin('D205'));
    }

    public function testUnknown()
    {
        // Test preserve unknown for varint.
        $m = new TestMessage();
        $from = hex2bin('F80601');  // TODO(teboring): Add a util to encode
                                    // varint for better readability
        $m->mergeFromString($from);
        $to = $m->serializeToString();
        $this->assertSame(bin2hex($from), bin2hex($to));

        // Test preserve unknown for 64-bit.
        $m = new TestMessage();
        $from = hex2bin('F9060000000000000000');
        $m->mergeFromString($from);
        $to = $m->serializeToString();
        $this->assertSame(bin2hex($from), bin2hex($to));

        // Test preserve unknown for length delimited.
        $m = new TestMessage();
        $from = hex2bin('FA0600');
        $m->mergeFromString($from);
        $to = $m->serializeToString();
        $this->assertSame(bin2hex($from), bin2hex($to));

        // Test preserve unknown for 32-bit.
        $m = new TestMessage();
        $from = hex2bin('FD0600000000');
        $m->mergeFromString($from);
        $to = $m->serializeToString();
        $this->assertSame(bin2hex($from), bin2hex($to));

        // Test discard unknown in message.
        $m = new TestMessage();
        $from = hex2bin('F80601');
        $m->mergeFromString($from);
        $m->discardUnknownFields();
        $to = $m->serializeToString();
        $this->assertSame("", bin2hex($to));

        // Test discard unknown for singular message field.
        $m = new TestMessage();
        $from = hex2bin('8A0103F80601');
        $m->mergeFromString($from);
        $m->discardUnknownFields();
        $to = $m->serializeToString();
        $this->assertSame("8a0100", bin2hex($to));

        // Test discard unknown for repeated message field.
        $m = new TestMessage();
        $from = hex2bin('FA0203F80601');
        $m->mergeFromString($from);
        $m->discardUnknownFields();
        $to = $m->serializeToString();
        $this->assertSame("fa0200", bin2hex($to));

        // Test discard unknown for map message value field.
        $m = new TestMessage();
        $from = hex2bin("BA050708011203F80601");
        $m->mergeFromString($from);
        $m->discardUnknownFields();
        $to = $m->serializeToString();
        $this->assertSame("ba050408011200", bin2hex($to));

        // Test discard unknown for singular message field.
        $m = new TestMessage();
        $from = hex2bin('9A0403F80601');
        $m->mergeFromString($from);
        $m->discardUnknownFields();
        $to = $m->serializeToString();
        $this->assertSame("9a0400", bin2hex($to));
    }

    public function testJsonEncode()
    {
        $from = new TestMessage();
        $this->setFields($from);
        $data = $from->serializeToJsonString();
        $to = new TestMessage();
        $to->mergeFromJsonString($data);
        $this->expectFields($to);
    }

    public function testDecodeDuration()
    {
        $m = new Google\Protobuf\Duration();
        $m->mergeFromJsonString("\"1234.5678s\"");
        $this->assertEquals(1234, $m->getSeconds());
        $this->assertEquals(567800000, $m->getNanos());
    }

    public function testEncodeDuration()
    {
        $m = new Google\Protobuf\Duration();
        $m->setSeconds(1234);
        $m->setNanos(999999999);
        $this->assertEquals("\"1234.999999999s\"", $m->serializeToJsonString());
    }

    public function testDecodeTimestamp()
    {
        $m = new Google\Protobuf\Timestamp();
        $m->mergeFromJsonString("\"2000-01-01T00:00:00.123456789Z\"");
        $this->assertEquals(946684800, $m->getSeconds());
        $this->assertEquals(123456789, $m->getNanos());
    }

    public function testEncodeTimestamp()
    {
        $m = new Google\Protobuf\Timestamp();
        $m->setSeconds(946684800);
        $m->setNanos(123456789);
        $this->assertEquals("\"2000-01-01T00:00:00.123456789Z\"",
                            $m->serializeToJsonString());
    }

    public function testDecodeTopLevelValue()
    {
        $m = new Value();
        $m->mergeFromJsonString("\"a\"");
        $this->assertSame("a", $m->getStringValue());

        $m = new Value();
        $m->mergeFromJsonString("1.5");
        $this->assertSame(1.5, $m->getNumberValue());

        $m = new Value();
        $m->mergeFromJsonString("true");
        $this->assertSame(true, $m->getBoolValue());

        $m = new Value();
        $m->mergeFromJsonString("null");
        $this->assertSame("null_value", $m->getKind());

        $m = new Value();
        $m->mergeFromJsonString("[1]");
        $this->assertSame("list_value", $m->getKind());

        $m = new Value();
        $m->mergeFromJsonString("{\"a\":1}");
        $this->assertSame("struct_value", $m->getKind());
    }

    public function testEncodeTopLevelValue()
    {
        $m = new Value();
        $m->setStringValue("a");
        $this->assertSame("\"a\"", $m->serializeToJsonString());

        $m = new Value();
        $m->setNumberValue(1.5);
        $this->assertSame("1.5", $m->serializeToJsonString());

        $m = new Value();
        $m->setBoolValue(true);
        $this->assertSame("true", $m->serializeToJsonString());

        $m = new Value();
        $m->setNullValue(0);
        $this->assertSame("null", $m->serializeToJsonString());
    }

    public function testDecodeTopLevelListValue()
    {
        $m = new ListValue();
        $m->mergeFromJsonString("[1]");
        $this->assertSame(1.0, $m->getValues()[0]->getNumberValue());
    }

    public function testEncodeTopLevelListValue()
    {
        $m = new ListValue();
        $arr = $m->getValues();
        $sub = new Value();
        $sub->setNumberValue(1.5);
        $arr[] = $sub;
        $this->assertSame("[1.5]", $m->serializeToJsonString());
    }

    public function testDecodeTopLevelStruct()
    {
        $m = new Struct();
        $m->mergeFromJsonString("{\"a\":{\"b\":1}}");
        $this->assertSame(1.0, $m->getFields()["a"]
                                 ->getStructValue()
                                 ->getFields()["b"]->getNumberValue());
    }

    public function testEncodeTopLevelStruct()
    {
        $m = new Struct();
        $map = $m->getFields();
        $sub = new Value();
        $sub->setNumberValue(1.5);
        $map["a"] = $sub;
        $this->assertSame("{\"a\":1.5}", $m->serializeToJsonString());
    }

}
