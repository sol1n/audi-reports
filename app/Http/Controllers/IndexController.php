<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Cache;
use Appercode\Backend;
use Appercode\User;
use Appercode\Form;
use Appercode\FormResponse;
use Appercode\Element;
use Carbon\Carbon;

class IndexController extends Controller
{
    private $user;

    private $formId;

    private $exampleId;

    public function __construct()
    {
        if (Cache::has('appercode-user')) {
            $this->user = Cache::get('appercode-user');
        } else {
            $token = config('appercode.token');
            $this->user = User::LoginByToken((new Backend), $token);
            Cache::put('appercode-user', $this->user, 20);
        }
    }

    private function getColors($key): array
    {
        $colors = ['orange', 'green', 'blue', 'brown', 'red'];
        $trasparent = 'rgba(0,0,0,0)';

        if ($key < count($colors)) {
            return [
                'main' => $colors[$key],
                'background' => $trasparent
            ];
        }

        $random = 'rgba(' . rand(0, 255) . ',' . rand(0, 255) . ',' . rand(0,255) . ',';
        return [
            'main' => $random . '0.4)',
            'background' => $trasparent
        ];
    }

    private function getResults(Carbon $date)
    {
        $formIds = [
            '3221ff92-07ee-45c6-95ab-c9ca0ce67335',
            'a9bc5c3d-9dc0-44ee-93cd-d5e54ce7c6c2',
            '13bd411a-f57f-4057-9f6e-7de3a90842f8'
        ];

        $forms = $form = Form::list($this->user->backend, [
            'where' => [
                'id' => [
                    '$in' => $formIds
                ]
            ]
        ])->mapWithKeys(function(Form $form) {
            $form->title = str_replace('Тест-драйв ', '', $form->title['ru']);
            return [$form->id =>$form];
        });

        $responses = FormResponse::list($this->user->backend, [
            'take' => -1,
            'where' => [
                'formId' => [
                    '$in' => $formIds
                ],
                'submittedAt' => [
                    '$gte' => $date->startOfDay()->toAtomString(),
                    '$lte' => $date->endOfDay()->toAtomString()
                ]
            ]
        ]);

        $parts = [
            'suspension' => [
                'title' => 'Работа подвески',
                'questions' => [
                    'control_0_0_k74qbdab' => true,
                    'control_0_0_k74q1k84' => true,
                    'control_0_0_k74pqlwb' => true,
                ],
            ],
            'comfort' => [
                'title' => 'Общий комфорт',
                'questions' => [
                    'control_0_1_k74qbx2a' => true,
                    'control_0_1_k74q2q59' => true,
                    'control_0_1_k74psa4h' => true,
                ],
            ],
            'back' => [
                'title' => 'Комфорт задних пассажиров',
                'questions' => [
                    'control_0_2_k74qcgfi' => true,
                    'control_0_2_k74q3m8f' => true,
                    'control_0_2_k74pt85m' => true,
                ],
            ],
            'multimedia' => [
                'title' => 'Оснащение и мультимедиа',
                'questions' => [
                    'control_0_3_k74qcwje' => true,
                    'control_0_3_k74q41xc' => true,
                    'control_0_3_k74pu1vt' => true,
                ]
            ],
            'controllability' => [
                'title' => 'Управляемость и маневренность',
                'questions' => [
                    'control_0_4_k74qddtc' => true,
                    'control_0_4_k74q58p4' => true,
                    'control_0_4_k74pus4q' => true,
                ]
            ],
            'ergonomics' => [
                'title' => 'Эргономика автомобиля',
                'questions' => [
                    'control_0_5_k74qdspr' => true,
                    'control_0_5_k74q6c7t' => true,
                    'control_0_5_k74pvbyt' => true,
                ],
            ],
            'universality' => [
                'title' => 'Универсальность в повседневных условиях',
                'questions' => [
                    'control_0_6_k74qehxy' => true,
                    'control_0_6_k74q6qum' => true,
                    'control_0_6_k74pvslh' => true,
                ]
            ],
            'engine' => [
                'title' => 'Работа двигателя и КПП',
                'questions' => [
                    'control_0_7_k74qf37c' => true,
                    'control_0_7_k74q7a6s' => true,
                    'control_0_7_k74pwg9o' => true,
                ]
            ]
        ];

        $counter = 0;
        $results = [];
        foreach ($formIds as $formId) {
            $results[$formId] = [
                'formId' => $formId,
                'title' => $forms[$formId]->title,
                'results' => [
                    'suspension' => [],
                    'comfort' => [],
                    'back' => [],
                    'multimedia' => [],
                    'controllability' => [],
                    'ergonomics' => [],
                    'universality' => [],
                    'engine' => []
                ],
                'index' => $counter++
            ];
        }

        foreach ($responses as $response) {
            foreach ($response->response as $k => $answer) {
                $index = null;
                foreach ($parts as $partIndex => $part) {
                    if (isset($part['questions'][$k])) {
                        $index = $partIndex;
                    }
                }
                $results[$response->formId]['results'][$index][] = $answer;
            }
        }

        foreach ($results as $formId => $result) {
            foreach ($result['results'] as $index => $values) {
                $result[$formId]['results'][$index] = 0;
                if (count($values)) {
                    $results[$formId]['results'][$index] = round(array_sum($values) / count($values), 2);
                }
            }
        }

        $datasets = [];

        foreach ($results as $formId => $result) {
            $data = [];
            $colors = $this->getColors($result['index']);
            $data = [
                $result['results']['suspension'],
                $result['results']['comfort'],
                $result['results']['back'],
                $result['results']['multimedia'],
                $result['results']['controllability'],
                $result['results']['ergonomics'],
                $result['results']['universality'],
                $result['results']['engine'],
            ];

            $datasets[] = [
                'label' => $result['title'],
                'borderColor' => $colors['main'],
                'backgroundColor' => $colors['background'],
                'data' => $data
            ];
        }

        return [
            'labels' => [
                $parts['suspension']['title'],
                $parts['comfort']['title'],
                $parts['back']['title'],
                $parts['multimedia']['title'],
                $parts['controllability']['title'],
                $parts['ergonomics']['title'],
                $parts['universality']['title'],
                $parts['engine']['title'],
            ],
            'datasets' => array_reverse($datasets)
        ];
    }

    public function index()
    {
        $day = request()->has('date')
            ? Carbon::parse(request()->get('date'), 'Europe/Moscow')
            : Carbon::now();

        $beginDate = Carbon::parse('2020-03-02', 'Europe/Moscow');
        $endDate = Carbon::parse('2020-03-28', 'Europe/Moscow');

        $dates = [];
        while ($beginDate < $endDate) {
            $dates[] = $beginDate->copy();
            $beginDate = $beginDate->addDays(1);
        }

        $results = $this->getResults($day);

        return view('index', [
            'data' => $results,
            'title' => 'Тест-драйв',
            'dates' => $dates,
            'currentDay' => $day
        ]);
    }
}
