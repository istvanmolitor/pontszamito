<?php

declare(strict_types=1);

namespace App\Controllers;

use App\ApiResource;
use App\Controller;
use App\Enums\UniversityProgram;
use App\ScoreCalculator;
use Exception;

final class ScoreCalculatorController extends Controller
{
    public function calculate(): ApiResource
    {
        $data = $this->getJsonBody();

        try {
            // Validate and get university program
            if (!isset($data['universityProgram']) || !is_string($data['universityProgram'])) {
                return $this->errorResponse('Az egyetemi szak kiválasztása kötelező.', 400);
            }

            $universityProgramEnum = UniversityProgram::tryFrom($data['universityProgram']);
            if ($universityProgramEnum === null) {
                return $this->errorResponse('Érvénytelen egyetemi szak.', 400);
            }

            $universityProgramObject = $universityProgramEnum->getProgramInstance();
            $calculator = new ScoreCalculator($universityProgramObject);

            // Add exam subject results
            if (isset($data['subjects']) && is_array($data['subjects'])) {
                foreach ($data['subjects'] as $subject) {
                    if (isset($subject['name'], $subject['level'], $subject['evaluation'])) {
                        $calculator->addExamSubjectResult(
                            (string)$subject['name'],
                            (string)$subject['level'],
                            (int)$subject['evaluation']
                        );
                    }
                }
            }

            // Add language exams
            if (isset($data['languageExams']) && is_array($data['languageExams'])) {
                foreach ($data['languageExams'] as $exam) {
                    if (isset($exam['language'], $exam['examType'])) {
                        $calculator->addLanguageExam(
                            (string)$exam['language'],
                            (string)$exam['examType']
                        );
                    }
                }
            }

            // Calculate and return result
            return $this->resource([
                'subjects' => $calculator->getExamSubjectResults(),
                'languageExams' => $calculator->getLanguageExams(),
                'totalScore' => $calculator->calculateTotalScore(),
            ]);

        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }
}

