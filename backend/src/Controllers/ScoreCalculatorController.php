<?php

declare(strict_types=1);

namespace App\Controllers;

use App\ApiResource;
use App\Controller;
use App\ScoreCalculator;
use App\Exceptions\MandatorySubjectsMissingException;
use Exception;

final class ScoreCalculatorController extends Controller
{
    public function calculate(): ApiResource
    {
        $data = $this->getJsonBody();

        try {
            $calculator = new ScoreCalculator();

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
                            (string)$exam['examType'],
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

        } catch (MandatorySubjectsMissingException $e) {
            return $this->errorResponse($e->getMessage(), 400);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }
}

