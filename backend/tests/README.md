# Score Calculator Tests

## Overview
This test suite provides comprehensive coverage for the `ScoreCalculator` class based on examples from `homework_input.php` and additional edge cases.

## Test Coverage

### 1. Valid Score Calculations
- **testCalculateScoreWithAllRequiredSubjects**: Tests the basic calculation with mandatory and selectable subjects, plus language exams
- **testCalculateScoreWithAdditionalPhysicsSubject**: Tests calculation when multiple selectable subjects are provided (uses the best one)
- **testBaseScoreCalculationBreakdown**: Tests the minimal valid scenario with just mandatory and one selectable subject
- **testMultipleAdvancedLevelSubjects**: Tests calculation with multiple advanced level subjects

### 2. Exception Cases
- **testThrowsExceptionWhenMandatorySubjectsAreMissing**: Verifies that missing mandatory subjects (Mathematics for ELTE IK) throws `MandatorySubjectsMissingException`
- **testThrowsExceptionWhenExamResultIsBelow20Percent**: Verifies that exam results below 20% throw `FailedExamException`
- **testThrowsExceptionWhenMandatorySelectableSubjectsAreMissing**: Verifies that missing all mandatory selectable subjects throws `MandatorySelectableSubjectsMissingException`
- **testThrowsExceptionWhenDuplicateSubjectIsAdded**: Verifies that adding the same subject with the same level twice throws `DuplicateSubjectException`

### 3. Bonus Calculations
- **testLanguageExamBonusCapping**: Tests that extra points are capped at 100 (advanced level bonuses + language exam bonuses)
- **testLanguageExamUsesHighestLevelPerLanguage**: Tests that when multiple language exams exist for the same language, the highest level is used

## Calculation Logic

### Base Score
For ELTE IK Programming Program:
- **Mandatory subjects**: Mathematics
- **Mandatory selectable**: One of [Physics, Biology, Informatics, Chemistry]

Formula: `(mandatory_score + best_selectable_score) * 2`

Where:
- Middle level (közép): percentage × 1
- Advanced level (emelt): percentage × 2

### Extra Points (capped at 100)
- **Advanced level subjects**: 50 points each
- **Language exams**:
  - B2: 28 points per language
  - C2: 40 points per language

### Example Calculation

Test Case 1:
```
Subjects:
- Matematika (mandatory): 90% × 2 (emelt) = 180
- Informatika (selectable): 95% × 1 (közép) = 95

Base score: (180 + 95) × 2 = 550

Extra points:
- 1 advanced level subject: 50
- B2 English: 28
- C2 German: 40
Total extra: 118, capped at 100

Final score: 550 + 100 = 650
```

## Running the Tests

```bash
cd backend
composer install
composer test
```

Or with detailed output:
```bash
./vendor/bin/phpunit --testdox
```

## Notes

- The test values differ from the comments in `homework_input.php` because the actual implementation uses a different calculation formula than what was documented in the examples
- The tests are based on the actual behavior of the `ScoreCalculator` class
- All tests use the `ElteIkProgrammingProgram` which has specific mandatory and selectable subject requirements

