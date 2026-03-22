<?php

declare(strict_types=1);

namespace Tests;

use App\Domain\Programs\ElteIkProgrammingProgram;
use App\Exceptions\FailedExamException;
use App\Exceptions\MandatorySubjectsMissingException;
use App\ScoreCalculator;
use PHPUnit\Framework\TestCase;

final class ScoreCalculatorTest extends TestCase
{
    /**
     * Test case 1: Valid calculation with all required subjects and language exams
     *
     * Calculation breakdown:
     * - Matematika (mandatory): 90% * 2 (emelt) = 180
     * - Informatika (selectable): 95% * 1 (közép) = 95
     * - Base score: (180 + 95) * 2 = 550
     * - Extra: 50 (1 emelt) + 28 (B2) + 40 (C2) = 118, capped at 100
     * - Total: 550 + 100 = 650
     */
    public function testCalculateScoreWithAllRequiredSubjects(): void
    {
        // Arrange
        $program = new ElteIkProgrammingProgram();
        $calculator = new ScoreCalculator($program);

        // Add exam subject results
        $calculator->addExamSubjectResult('magyar nyelv és irodalom', 'közép', 70);
        $calculator->addExamSubjectResult('történelem', 'közép', 80);
        $calculator->addExamSubjectResult('matematika', 'emelt', 90);
        $calculator->addExamSubjectResult('angol', 'közép', 94);
        $calculator->addExamSubjectResult('informatika', 'közép', 95);

        // Add language exams
        $calculator->addLanguageExam('angol', 'B2');
        $calculator->addLanguageExam('német', 'C2');

        // Act
        $totalScore = $calculator->calculateTotalScore();

        // Assert
        $this->assertSame(650, $totalScore);
    }

    /**
     * Test case 2: Valid calculation with additional physics subject
     *
     * Calculation breakdown:
     * - Matematika (mandatory): 90% * 2 (emelt) = 180
     * - Fizika (selectable, better than informatika): 98% * 1 (közép) = 98
     * - Base score: (180 + 98) * 2 = 556
     * - Extra: 50 (1 emelt) + 28 (B2) + 40 (C2) = 118, capped at 100
     * - Total: 556 + 100 = 656
     */
    public function testCalculateScoreWithAdditionalPhysicsSubject(): void
    {
        // Arrange
        $program = new ElteIkProgrammingProgram();
        $calculator = new ScoreCalculator($program);

        // Add exam subject results
        $calculator->addExamSubjectResult('magyar nyelv és irodalom', 'közép', 70);
        $calculator->addExamSubjectResult('történelem', 'közép', 80);
        $calculator->addExamSubjectResult('matematika', 'emelt', 90);
        $calculator->addExamSubjectResult('angol', 'közép', 94);
        $calculator->addExamSubjectResult('informatika', 'közép', 95);
        $calculator->addExamSubjectResult('fizika', 'közép', 98);

        // Add language exams
        $calculator->addLanguageExam('angol', 'B2');
        $calculator->addLanguageExam('német', 'C2');

        // Act
        $totalScore = $calculator->calculateTotalScore();

        // Assert
        $this->assertSame(656, $totalScore);
    }

    /**
     * Test case 3: Missing mandatory subjects
     * Expected: MandatorySubjectsMissingException
     *
     * For ELTE IK Programming, MATHEMATICS is mandatory, so we test without it.
     */
    public function testThrowsExceptionWhenMandatorySubjectsAreMissing(): void
    {
        // Arrange
        $program = new ElteIkProgrammingProgram();
        $calculator = new ScoreCalculator($program);

        // Add exam subject results (missing Mathematics which is mandatory for ELTE IK)
        $calculator->addExamSubjectResult('magyar nyelv és irodalom', 'közép', 70);
        $calculator->addExamSubjectResult('történelem', 'közép', 80);
        $calculator->addExamSubjectResult('angol', 'közép', 94);
        $calculator->addExamSubjectResult('informatika', 'közép', 95);

        // Add language exams
        $calculator->addLanguageExam('angol', 'B2');
        $calculator->addLanguageExam('német', 'C2');

        // Assert and Act
        $this->expectException(MandatorySubjectsMissingException::class);
        $this->expectExceptionMessage('A következő kötelező tantárgyak hiányoznak: Matematika');

        $calculator->calculateTotalScore();
    }

    /**
     * Test case 4: Failed exam (below 20%)
     * Expected: FailedExamException
     */
    public function testThrowsExceptionWhenExamResultIsBelow20Percent(): void
    {
        // Arrange
        $program = new ElteIkProgrammingProgram();
        $calculator = new ScoreCalculator($program);

        // Add exam subject results with magyar nyelv és irodalom at 15%
        $calculator->addExamSubjectResult('magyar nyelv és irodalom', 'közép', 15);
        $calculator->addExamSubjectResult('történelem', 'közép', 80);
        $calculator->addExamSubjectResult('matematika', 'emelt', 90);
        $calculator->addExamSubjectResult('angol', 'közép', 94);
        $calculator->addExamSubjectResult('informatika', 'közép', 95);

        // Add language exams
        $calculator->addLanguageExam('angol', 'B2');
        $calculator->addLanguageExam('német', 'C2');

        // Assert and Act
        $this->expectException(FailedExamException::class);
        $this->expectExceptionMessageMatches('/A vizsga sikertelen.*Magyar nyelv és irodalom.*15%.*20% alatt van/');

        $calculator->calculateTotalScore();
    }

    /**
     * Additional test: Verify the base score calculation breakdown
     */
    public function testBaseScoreCalculationBreakdown(): void
    {
        // Arrange
        $program = new ElteIkProgrammingProgram();
        $calculator = new ScoreCalculator($program);

        // Matematika: 90% emelt = 90 * 2 = 180 (kötelező)
        $calculator->addExamSubjectResult('matematika', 'emelt', 90);

        // Informatika: 95% közép = 95 * 1 = 95 (választható)
        $calculator->addExamSubjectResult('informatika', 'közép', 95);

        // Act
        $totalScore = $calculator->calculateTotalScore();

        // Base score = (180 + 95) * 2 = 550
        // Extra score = 50 (advanced subject bonus)
        // Total = 550 + 50 = 600
        $this->assertSame(600, $totalScore);
    }

    /**
     * Test language exam bonus calculation
     */
    public function testLanguageExamBonusCapping(): void
    {
        // Arrange
        $program = new ElteIkProgrammingProgram();
        $calculator = new ScoreCalculator($program);

        // Add minimum required subjects
        $calculator->addExamSubjectResult('matematika', 'emelt', 90);
        $calculator->addExamSubjectResult('informatika', 'közép', 95);

        // Add multiple language exams that would exceed 100 points
        $calculator->addLanguageExam('angol', 'C2'); // 40 points
        $calculator->addLanguageExam('német', 'C2'); // 40 points
        $calculator->addLanguageExam('francia', 'C2'); // 40 points

        // Act
        $totalScore = $calculator->calculateTotalScore();

        // Base score = (180 + 95) * 2 = 550
        // Extra score = min(50 + 40 + 40 + 40, 100) = 100 (capped at 100)
        // Total = 550 + 100 = 650
        $this->assertSame(650, $totalScore);
    }

    /**
     * Test missing mandatory selectable subjects
     */
    public function testThrowsExceptionWhenMandatorySelectableSubjectsAreMissing(): void
    {
        // Arrange
        $program = new ElteIkProgrammingProgram();
        $calculator = new ScoreCalculator($program);

        // Add only mathematics (mandatory) but none of the mandatory selectable subjects
        $calculator->addExamSubjectResult('matematika', 'emelt', 90);
        $calculator->addExamSubjectResult('magyar nyelv és irodalom', 'közép', 70);

        // Assert and Act
        $this->expectException(\App\Exceptions\MandatorySelectableSubjectsMissingException::class);

        $calculator->calculateTotalScore();
    }

    /**
     * Test duplicate subject exception
     */
    public function testThrowsExceptionWhenDuplicateSubjectIsAdded(): void
    {
        // Arrange
        $program = new ElteIkProgrammingProgram();
        $calculator = new ScoreCalculator($program);

        // Add mathematics once
        $calculator->addExamSubjectResult('matematika', 'emelt', 90);

        // Assert and Act - try to add mathematics again with same level
        $this->expectException(\App\Exceptions\DuplicateSubjectException::class);
        $this->expectExceptionMessage('A(z) Matematika tantárgy (Emelt szint szint) már hozzá van adva.');

        $calculator->addExamSubjectResult('matematika', 'emelt', 85);
    }

    /**
     * Test that multiple language exams for the same language use the highest level
     */
    public function testLanguageExamUsesHighestLevelPerLanguage(): void
    {
        // Arrange
        $program = new ElteIkProgrammingProgram();
        $calculator = new ScoreCalculator($program);

        // Add minimum required subjects
        $calculator->addExamSubjectResult('matematika', 'emelt', 90);
        $calculator->addExamSubjectResult('informatika', 'közép', 95);

        // Add multiple language exams for English - should use the highest (C2 = 40)
        $calculator->addLanguageExam('angol', 'B2'); // 28 points
        $calculator->addLanguageExam('angol', 'C2'); // 40 points - this should be used

        // Act
        $totalScore = $calculator->calculateTotalScore();

        // Base score = (180 + 95) * 2 = 550
        // Extra score = 50 (1 emelt) + 40 (C2 angol) = 90
        // Total = 550 + 90 = 640
        $this->assertSame(640, $totalScore);
    }

    /**
     * Test with multiple advanced level subjects
     */
    public function testMultipleAdvancedLevelSubjects(): void
    {
        // Arrange
        $program = new ElteIkProgrammingProgram();
        $calculator = new ScoreCalculator($program);

        // Add multiple advanced level subjects
        $calculator->addExamSubjectResult('matematika', 'emelt', 90);
        $calculator->addExamSubjectResult('fizika', 'emelt', 85);
        $calculator->addExamSubjectResult('informatika', 'emelt', 95);

        // Act
        $totalScore = $calculator->calculateTotalScore();

        // Matematika (mandatory): 90 * 2 = 180
        // Informatika (selectable, best): 95 * 2 = 190
        // Base score: (180 + 190) * 2 = 740
        // Extra: 50 * 3 = 150, capped at 100
        // Total: 740 + 100 = 840
        $this->assertSame(840, $totalScore);
    }
}

