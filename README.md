# Airflow Dag run Symfony bundle

This bundle provide a way to trigger new dag on [Apache Airflow](https://airflow.apache.org/) run to generate export files and request asynchronously for 
the generated export filename.

## Installation

With composer:

```bash
composer require bluspark/airflow-dag-run-bundle
```

## Configuration

Make sure your bundle has been enabled in your `config/bundles.php` file (automatically done if you're using Symfony Flex)
```php
# config/bundles.php
return [
    ...
    Bluspark\AirflowDagRunBundle\BlusparkAirflowDagRunBundle::class => ['all' => true],
];
```

Add a `bluspark_airflow_dag_run.yaml` file in your `config/packages` directory to define the following required parameters:
```yaml
bluspark_airflow_dag_run:
  airflow_host: https://from-config.example.org
  airflow_dag_id: your-dag-id
  airflow_username: user
  airflow_password: !ChangeMe!
```
You can have more details about the configuration parameters by running this command in your Symfony project:
```bash
bin/console config:dump bluspark_airflow_dag_run
```

Finally, add in your `config/packages/messenger.yaml` file the transport of your choice which will handle the success message dispatched by the bundle (as explained below):
```yaml
framework:
  messenger:

    routing:
      'Bluspark\AirflowDagRunBundle\Scheduler\Message\DagRunMessageExecuted': my_project_transport

```

> **If you're using Symfony < 6.4**, the bundle won't use `Scheduler` but a standard message dispatched through a `MessengerBus` instead.  
> If so, you must declare **all** the bundl'es messages for the transport management :
> ```yaml
>framework:
>  messenger:
>
>    routing:
>      'Bluspark\AirflowDagRunBundle\Scheduler\Message\*': my_project_transport
>
> ```

## Usage
The bundle provide a bridge service class that you can use in your project through using dependency injection.
```php
namespace App\Controller;

use Bluspark\AirflowDagRunBundle\Contracts\Bridge\AirflowDagBridgeInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class YourController extends AbstractController
{
    #[Route(path: '/my-export-route')]
    public function __invoke(AirflowDagBridgeInterface $airflowDagBridge): Response
    {
        // ... build your $config array with your export parameters
        $dagRunStatus = $airflowDagBridge->requestNewExportFile($config);
    }
}
```

Authorized parameters for the export request are:  
- `format`: format expected for the export file (eg: "csv", "xls")
- `export`: data type for your export (eg: 'pickup', 'producer', ...)
- `search`: array of filters you want to apply for data included in your export file
- `extra`: array of data that you want to use or pass throughout all the export process (e.g. an email to notify on success)

No other configuration parameters are considered valid.  
Once the export file has been requested on Airflow, the bundle uses a [Scheduler](https://symfony.com/doc/current/scheduler.html) recurring message to check every 30 seconds if the file has been successfully created, with its own handler.
Then, the bundle dispatch using [Symfony Messenger](https://symfony.com/doc/current/messenger.html) component a `Bluspark\AirflowDagRunBundle\Message\DagRunMessageExecuted` message with a `filename` property containing the now available file on S3
You will have to implement the handler for the `Bluspark\AirflowDagRunBundle\Message\DagRunMessageExecuted` message with your own logic inside your project.  

To run the Scheduler transport used by this bundle, do not forget to run the following command (**only for Symfony version >= 6.4**)
```bash
bin/console messenger:consume scheduler_airflow_dag_run
```

## Messages
The bundle implements 2 different messages that need to be consumed : 
- a `Bluspark\AirflowDagRunBundle\Message\DagRunChecker` message meant to be consumed by the `scheduler_airflow_dag_run` Schedule (provided by the bundle)
- a `Bluspark\AirflowDagRunBundle\Message\DagRunMessageExecuted` message meant to be consumed by the Messenger transport of your choice in your project

## License

This project is licensed under the CeCILL-B License - see the [LICENSE](LICENSE) file for details.

## Sponsors

![Bluspark logo](./docs/bluspark_logo.jpeg)

Bluspark is a Saas application to operate infrastructure of agglomerations and cities. It is a complete solution to manage the life cycle of your infrastructure, from the design to the maintenance.

![Consoneo logo](./docs/consoneo_logo.jpeg)

The digital SaaS platform for financing and managing energy renovation aid