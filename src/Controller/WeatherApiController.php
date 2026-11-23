<?php

namespace App\Controller;

use App\Entity\Measurement;
use App\Service\WeatherUtil;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;

class WeatherApiController extends AbstractController
{
    #[Route('/api/v1/weather', name: 'app_weather_api', methods: ['GET'])]
    public function index(
        WeatherUtil $weatherUtil,
        #[MapQueryParameter] string $country,
        #[MapQueryParameter] string $city,
        #[MapQueryParameter] string $format = 'json',
        #[MapQueryParameter('twig')] bool $twig = false
    ): Response {
        $measurements = $weatherUtil->getWeatherForCountryAndCity(
            $country,
            $city
        );

        if ($twig && $format === 'csv') {
            $response = $this->render('weather_api/index.csv.twig', [
                'city' => $city,
                'country' => $country,
                'measurements' => $measurements,
            ]);
            $response->headers->set('Content-Type', 'text/plain; charset=UTF-8');

            return $response;
        }

        if ($twig && $format === 'json') {
            $response = $this->render('weather_api/index.json.twig', [
                'city' => $city,
                'country' => $country,
                'measurements' => $measurements,
            ]);
            $response->headers->set('Content-Type', 'application/json; charset=UTF-8');

            return $response;
        }

        if ($format === 'csv') {
            $rows = [];

            $rows[] = [
                'city',
                'country',
                'date',
                'celsius',
                'fahrenheit',
                'humidity',
                'cloudCover',
                'airPressure',
                'precipitation',
            ];

            foreach ($measurements as $m) {
                $rows[] = [
                    $city,
                    $country,
                    $m->getDate()->format('Y-m-d'),
                    $m->getTemperature(),
                    $m->getFahrenheit(),
                    $m->getHumidity(),
                    $m->getCloudCover(),
                    $m->getAirPressure(),
                    $m->getPrecipitation(),
                ];
            }

            $csv = implode("\r\n", array_map(
                fn($row) => implode(',', $row),
                $rows
            ));

            return new Response($csv, 200, [
                'Content-Type' => 'text/csv; charset=UTF-8',
            ]);
        }

        return $this->json([
            'city' => $city,
            'country' => $country,
            'measurements' => array_map(fn(Measurement $m) => [
                'date' => $m->getDate()->format('Y-m-d'),
                'celsius' => $m->getTemperature(),
                'fahrenheit' => $m->getFahrenheit(),
                'humidity' => $m->getHumidity(),
                'cloudCover' => $m->getCloudCover(),
                'airPressure' => $m->getAirPressure(),
                'precipitation' => $m->getPrecipitation(),
            ], $measurements),
        ]);
    }
}
