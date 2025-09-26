<?php

namespace App\Services\Sap;

use RuntimeException;
use SAPNWRFC\Connection as SapConnection;
use SAPNWRFC\Exception as SapException;

/**
 * Clase para manejar conexiones y llamadas a funciones RFC de SAP.
 * 
 * Implementa un patrón Singleton para reutilizar la misma instancia
 * de conexión mientras se ejecuta el proceso.
 */
final class SapRfc {

    /**
     * Instancia única de la clase (Singleton).
     *
     * @var SapRfc|null
     */
    private static ?SapRfc $instance = null;

    /**
     * Objeto de conexión a SAP.
     *
     * @var SapConnection|null
     */
    private ?SapConnection $con = null;

    /**
     * Credenciales de conexión a SAP.
     *
     * @var array<string,mixed>
     */
    private array $credentials;

    /**
     * Nombre de la función RFC a ejecutar en SAP.
     *
     * @var string
     */
    private string $name_function;

    /**
     * Constructor privado.
     *
     * Carga las credenciales de configuración y abre la conexión con SAP.
     */
    private function __construct() {
        $this->credentials = config('sap');
        $this->connection($this->credentials);
    }

    /**
     * Establece la conexión con SAP.
     *
     * @param array<string,mixed> $credentials Arreglo con las credenciales de conexión.
     * @return void
     */
    private function connection(array $credentials): void {
        try {
            $this->con = new SapConnection($credentials);
        } catch (SapException $e) {
            throw new RuntimeException("No se pudo establecer la conexión con SAP: " . $e->getMessage());
        }
    }

    /**
     * Define el nombre de la función RFC que se desea ejecutar.
     * Si no existe instancia, la crea.
     *
     * @param string $name Nombre de la función RFC en SAP.
     * @return self
     */
    public static function rfc_name(string $name): self {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        self::$instance->name_function = $name;
        return self::$instance;
    }

    /**
     * Ejecuta la función RFC previamente definida con parámetros.
     *
     * @param array<string,mixed> $params Parámetros de entrada para la función RFC.
     * @return mixed Resultado de la función SAP o mensaje de error.
     */
    public function params(array $params): mixed {

        if ($this->name_function === null) {
            throw new RuntimeException("No se ha definido una función RFC a ejecutar.");
        }

        try {
            $function = $this->con->getFunction($this->name_function);
            $result = $function->invoke($params);
            return $result;
        } catch (SapException $ex) {
            throw new RuntimeException("Error al ejecutar la función RFC '{$this->name_function}': " . $ex->getMessage());
        }
    }

    /**
     * Cierra la conexión con SAP si existe.
     *
     * @return void
     */
    public function disconnect(): void {
        if ($this->con !== null) {
            $this->con->close();
            $this->con = null;
        }
    }

    /**
     * Destructor.
     *
     * Se asegura de cerrar la conexión al finalizar la ejecución del objeto.
     */
    public function __destruct() {
        $this->disconnect();
    }
}
