<?php
declare (strict_types=1);

namespace App\Validators;

use Respect\Validation\ValidatorBuilder as v;
//use Respect\Validation\ExceptionsValidationException;

final class PacienteValidator {
    private function reglas() : array {
        return [
            'cedula' => [
                'regla' => v::stringType()->notBlank()->regex('/^\d{9,12}$/'),
                'mensaje' => 'La cédula es obligatoria y debe contener solo dígitos, entre 9 y 12 caracteres.',
            ],
            'nombre_completo' => [
                'regla' => v::stringType()
                    ->notBlank()
                    ->length(v::between(11, 150))
                    ->regex('/^[A-Za-zÑñÁÉÍÓÚáéíóú]{2,}(?: [A-Za-zÑñÁÉÍÓÚáéíóú]{2,}){1,3}$/u'),
                'mensaje' => 'El nombre completo es obligatorio y debe tener entre 11 y 150 caracteres, solo letras y espacios.',
            ],
            'fecha_nacimiento' => [
                'regla' => v::stringType()->notBlank()->date('Y-m-d'),
                'mensaje' => 'La fecha de nacimiento es obligatoria y debe ser una fecha válida en formato YYYY-MM-DD.',
            ],
            'username' => [
                'regla' => v::stringType()->notBlank()->length(v::between(8, 64))->regex('/^[a-z0-9]{8,16}$/'),
                'mensaje' => 'El username debe tener entre 8 y 64 caracteres y solo letras minúsculas y números, sin espacios.',
            ],
            'password' => [
                'regla' => v::stringType()
                    ->notBlank()
                    ->length(v::between(8, 64))
                    ->regex('/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[#$@*])[\s\S]{8,16}$/'),
                'mensaje' => 'La contraseña debe tener entre 8 y 16 caracteres, incluir mayúscula, minúscula, número y un símbolo especial. (#$@*)',
            ],
            'telefono' => [
                'regla' => v::stringType()->regex('/^[2-9][0-9]{3}-[0-9 ]{4}$/')->length(v::equals(9)),
                'mensaje' => 'El teléfono debe tener el formato ####-#### incluyendo el guión.',
            ],
        ];
    }

    public function validate(array $data): array {
        $errores = [];

        foreach ($this->reglas() as $campo => $config) {
            $valor = $data[$campo] ?? null;
            $regla = $config['regla'];
            $mensaje = $config['mensaje'];
            if ($campo === 'telefono' && ($valor === null || $valor === ""))
                continue;
            if ($regla->validate($valor)->hasFailed()) {
                $errores[$campo] = $mensaje;
            }
        }

        return $errores;
    }
}
