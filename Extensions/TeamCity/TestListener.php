<?php
/**
 * @author Alexander Ilyin
 * @url    http://confluence.jetbrains.net/display/TCD7/Build+Script+Interaction+with+TeamCity#BuildScriptInteractionwithTeamCity-ReportingTests
 */
class PHPUnit_Extensions_TeamCity_TestListener extends PHPUnit_Util_Printer implements PHPUnit_Framework_TestListener
{
    /**
     * An error occurred.
     *
     * @todo Add check that $test is instance of PHPUnit_Framework_TestCase
     *
     * @param  PHPUnit_Framework_Test $test
     * @param  Exception              $e
     * @param  float                  $time
     */
    public function addError(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
        $message = sprintf("##teamcity[testFailed name='%s' message='%s' details='%s']" . PHP_EOL,
            $this->addslashes($test->getName()),
            $this->addslashes($e->getMessage()),
            $this->addslashes($e->getTraceAsString())
        );
        $this->write($message);
    }

    protected function addslashes($string){
         $search = array(
             "|",
             "'",
             "\n",
             "\r",
             "\u0085",
             "\u2028",
             "\u2029",
             "[",
             "]",
         );
         $replace = array(
             "||",
             "|'",
             "|n",
             "|r",
             "|x",
             "|l",
             "|p",
             "|[",
             "|]",
         );
         return str_replace($search, $replace, $string);
    }

    /**
     * A failure occurred.
     *
     * @todo Add check that $test is instance of PHPUnit_Framework_TestCase
     *
     * @param  PHPUnit_Framework_Test                 $test
     * @param  PHPUnit_Framework_AssertionFailedError $e
     * @param  float                                  $time
     */
    public function addFailure(PHPUnit_Framework_Test $test, PHPUnit_Framework_AssertionFailedError $e, $time)
    {
        $failures = array();
        $testResult = $test->getTestResultObject();
        /** @var $failure PHPUnit_Framework_TestFailure */
        foreach ($testResult->failures() as $failure) {
            $hash = "$e->getMessage() $e->getTraceAsString()";
            if(isset($failures[$hash])){
                continue;
            }
            /** @var $exception PHPUnit_Framework_ExpectationFailedException */
            $exception         = $failure->thrownException();
            $comparisonFailure = $exception->getComparisonFailure();
            if ($comparisonFailure instanceof PHPUnit_Framework_ComparisonFailure) {
                $message = sprintf("##teamcity[testFailed type='comparisonFailure' name='%s' message='%s' details='%s' expected='%s' actual='%s']" . PHP_EOL,
                    $this->addslashes($test->getName()),
                    $this->addslashes($e->getMessage()),
                    $this->addslashes($e->getTraceAsString()),
                    $this->addslashes($comparisonFailure->getExpectedAsString()),
                    $this->addslashes($comparisonFailure->getActualAsString())
                );
            } else {
                $message = sprintf("##teamcity[testFailed type='comparisonFailure' name='%s' message='%s' details='%s']" . PHP_EOL,
                    $this->addslashes($test->getName()),
                    $this->addslashes($e->getMessage()),
                    $this->addslashes($e->getTraceAsString())
                );
            }
            $this->write($message);
            $failures[$hash] = true;
        }
    }

    /**
     * Incomplete test.
     *
     * @todo Add check that $test is instance of PHPUnit_Framework_TestCase
     *
     * @param  PHPUnit_Framework_Test $test
     * @param  Exception              $e
     * @param  float                  $time
     */
    public function addIncompleteTest(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
        $message = sprintf("##teamcity[testIgnored name='%s' message='%s']" . PHP_EOL,
            $this->addslashes($test->getName()),
            $this->addslashes($e->getMessage())
        );
        $this->write($message);
    }

    /**
     * Skipped test.
     *
     * @todo   Add check that $test is instance of PHPUnit_Framework_TestCase
     *
     * @param  PHPUnit_Framework_Test $test
     * @param  Exception              $e
     * @param  float                  $time
     *
     * @since  Method available since Release 3.0.0
     */
    public function addSkippedTest(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
        $message = sprintf("##teamcity[testIgnored name='%s' message='%s']" . PHP_EOL,
            $this->addslashes($test->getName()),
            $this->addslashes($e->getMessage())
        );
        $this->write($message);
    }

    /**
     * A test suite started.
     *
     * @param  PHPUnit_Framework_TestSuite $suite
     *
     * @since  Method available since Release 2.2.0
     */
    public function startTestSuite(PHPUnit_Framework_TestSuite $suite)
    {
        $message = sprintf("##teamcity[testSuiteStarted name='%s']" . PHP_EOL,
            $this->addslashes($suite->getName())
        );
        $this->write($message);
    }

    /**
     * A test suite ended.
     *
     * @param  PHPUnit_Framework_TestSuite $suite
     *
     * @since  Method available since Release 2.2.0
     */
    public function endTestSuite(PHPUnit_Framework_TestSuite $suite)
    {
        $message = sprintf("##teamcity[testSuiteFinished name='%s']" . PHP_EOL,
            $this->addslashes($suite->getName())
        );
        $this->write($message);
    }

    /**
     * A test started.
     *
     * @todo Add check that $test is instance of PHPUnit_Framework_TestCase
     *
     * @param  PHPUnit_Framework_Test $test
     */
    public function startTest(PHPUnit_Framework_Test $test)
    {
        $message = sprintf("##teamcity[testStarted name='%s' captureStandardOutput='%s']" . PHP_EOL,
            $this->addslashes($test->getName()),
            'true'
        );
        $this->write($message);
    }

    /**
     * A test ended.
     *
     * @todo Add check that $test is instance of PHPUnit_Framework_TestCase
     *
     * @param  PHPUnit_Framework_Test     $test
     * @param  float                      $time
     */
    public function endTest(PHPUnit_Framework_Test $test, $time)
    {
        $message = sprintf("##teamcity[testFinished name='%s' duration='%s']" . PHP_EOL,
            $this->addslashes($test->getName()),
            $time
        );
        $this->write($message);
    }
}