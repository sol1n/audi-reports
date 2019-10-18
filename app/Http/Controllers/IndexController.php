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
            '2995531c-f572-46b6-932b-006fa860c52b',
            '144281bb-7982-452a-8bf0-cf14c2c14771',
            'ff98d4c8-5c7f-4ce5-9c97-21c50c76cc59',
            '63060da0-f3fa-438a-b01b-4cda5cb367f7',
            '7202aa43-c6fa-4928-84b2-9817fccd4f97'
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
                    'control_0_0_k1ug702y' => true,
                    'control_0_0_k1ug45vb' => true,
                    'control_0_0_k1ug18lj' => true,
                    'control_0_0_k1ufx2v0' => true,
                    'control_0_0_k1ua1vyo' => true,
                ],
            ],
            'comfort' => [
                'title' => 'Общий комфорт',
                'questions' => [
                    'control_0_1_k1ug75cf' => true,
                    'control_0_1_k1ug4b4u' => true,
                    'control_0_1_k1ug1fqt' => true,
                    'control_0_1_k1ufx9tu' => true,
                    'control_0_6_k1ugf7nv' => true,
                ],
            ],
            'back' => [
                'title' => 'Комфорт задних пассажиров',
                'questions' => [
                    'control_0_2_k1ug7bla' => true,
                    'control_0_2_k1ug4ea5' => true,
                    'control_0_2_k1ug1j4f' => true,
                    'control_0_2_k1ufxers' => true,
                    'control_0_7_k1ugg9ky' => true,
                ],
            ],
            'multimedia' => [
                'title' => 'Оснащение и мультимедиа',
                'questions' => [
                    'control_0_3_k1ug7e4j' => true,
                    'control_0_3_k1ug4h7e' => true,
                    'control_0_3_k1ug1mce' => true,
                    'control_0_3_k1ufxgl6' => true,
                    'control_0_1_k1ua2yiq' => true,
                ]
            ],
            'controllability' => [
                'title' => 'Управляемость и маневренность',
                'questions' => [
                    'control_0_4_k1ug7iyv' => true,
                    'control_0_4_k1ug4kbz' => true,
                    'control_0_4_k1ug1pi6' => true,
                    'control_0_4_k1ufxlnx' => true,
                    'control_0_2_k1ua33o6' => true,
                ]
            ],
            'ergonomics' => [
                'title' => 'Эргономика автомобиля',
                'questions' => [
                    'control_0_5_k1ug7n75' => true,
                    'control_0_5_k1ug4n33' => true,
                    'control_0_5_k1ug1sw6' => true,
                    'control_0_5_k1ufxp5q' => true,
                    'control_0_3_k1ua39bq' => true,
                ],
            ],
            'universality' => [
                'title' => 'Универсальность в повседневных условиях',
                'questions' => [
                    'control_0_6_k1ug7rdn' => true,
                    'control_0_6_k1ug4q34' => true,
                    'control_0_6_k1ug1xd3' => true,
                    'control_0_6_k1ufxsyq' => true,
                    'control_0_4_k1ua3dum' => true,
                ]
            ],
            'engine' => [
                'title' => 'Работа двигателя и КПП',
                'questions' => [
                    'control_0_7_k1ug7uob' => true,
                    'control_0_7_k1ug4tbt' => true,
                    'control_0_7_k1ug234a' => true,
                    'control_0_7_k1ufxwxk' => true,
                    'control_0_5_k1ua3hsp' => true,
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

        $beginDate = Carbon::parse('2019-10-18', 'Europe/Moscow');
        $endDate = Carbon::parse('2019-12-04', 'Europe/Moscow');

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
