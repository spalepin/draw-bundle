<?php

namespace Draw\DrawBundle\Command;

use Sensio\Bundle\GeneratorBundle\Command\AutoComplete\EntitiesAutoCompleter;
use Sensio\Bundle\GeneratorBundle\Command\GenerateDoctrineCommand;
use Sensio\Bundle\GeneratorBundle\Command\Validators;
use Draw\DrawBundle\Generator\RestControllerGenerator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;

class GenerateRestControllerCommand extends GenerateDoctrineCommand
{
    /**
     * @see Command
     */
    public function configure()
    {
        $this
            ->setName('draw:generate:controller:rest')
            ->setDefinition(array(
                new InputOption('controller', '', InputOption::VALUE_REQUIRED, 'The name of the controller to create'),
                new InputOption('controllerSubNamespace', '', InputOption::VALUE_REQUIRED, 'The sub namespace of the controller to add.'),
                new InputOption('entity', '', InputOption::VALUE_REQUIRED, 'The name of the entity to generate the controller for.'),
                new InputOption('entityPrefix', '', InputOption::VALUE_REQUIRED, 'A prefix to for the entity'),
                new InputOption('override', '', InputOption::VALUE_NONE, 'If we must override existing file.'),
            ))
            ->setDescription('Generates a crud rest controller')
            ->setHelp(<<<EOT
The <info>draw:generate:controller:rest</info> command helps you generates new rest controllers
inside bundles.

By default, the command interacts with the developer to tweak the generation.
Any passed option will be used as a default value for the interaction
(<comment>--bundle</comment> and <comment>--controller</comment> are the only
ones needed if you follow the conventions):

<info>php app/console generate:controller --controller=AcmeBlogBundle:Post</info>

If you want to disable any user interaction, use <comment>--no-interaction</comment>
but don't forget to pass all needed options:

<info>php app/console generate:controller --controller=AcmeBlogBundle:Post --no-interaction</info>

Every generated file is based on a template. There are default templates but they can
be overriden by placing custom templates in one of the following locations, by order of priority:

<info>BUNDLE_PATH/Resources/SensioGeneratorBundle/skeleton/controller
APP_PATH/Resources/SensioGeneratorBundle/skeleton/controller</info>

You can check https://github.com/sensio/SensioGeneratorBundle/tree/master/Resources/skeleton
in order to know the file structure of the skeleton
EOT
            )

        ;
    }

    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $questionHelper = $this->getQuestionHelper();

        if ($input->isInteractive()) {
            $question = new ConfirmationQuestion($questionHelper->getQuestion('Do you confirm generation', 'yes', '?'), true);
            if (!$questionHelper->ask($input, $output, $question)) {
                $output->writeln('<error>Command aborted</error>');

                return 1;
            }
        }

        $entity = Validators::validateEntityName($input->getOption('entity'));
        $controller = $input->getOption('controller');
        $entityPrefix = $input->getOption('entityPrefix');
        $controllerSubNamespace = $input->getOption('controllerSubNamespace');
        list($bundle, $entity) = $this->parseShortcutNotation($entity);

        $questionHelper->writeSection($output, 'Rest generation');

        $entityClass = $this->getContainer()->get('doctrine')->getAliasNamespace($bundle).'\\'.$entity;
        $bundle      = $this->getContainer()->get('kernel')->getBundle($bundle);

        $override = (bool)$input->getOption('override');

        $generator = $this->getGenerator($bundle);
        $generator->generate($bundle, $controller, $entityClass, $override, $entityPrefix, $controllerSubNamespace);

        $output->writeln('Generating the CRUD code: <info>OK</info>');

        $questionHelper->writeGeneratorSummary($output, []);

        return 0;
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $questionHelper = $this->getQuestionHelper();
        $questionHelper->writeSection($output, 'Welcome to the Doctrine2 CRUD generator');

        // namespace
        $output->writeln(array(
            '',
            'This command helps you generate CRUD controllers and templates.',
            '',
            'First, you need to give the entity for which you want to generate a CRUD.',
            'You can give an entity that does not exist yet and the wizard will help',
            'you defining it.',
            '',
            'You must use the shortcut notation like <comment>AcmeBlogBundle:Post</comment>.',
            '',
        ));

        if ($input->hasArgument('entity') && $input->getArgument('entity') != '') {
            $input->setOption('entity', $input->getArgument('entity'));
        }

        $question = new Question($questionHelper->getQuestion('The Entity shortcut name', $input->getOption('entity')), $input->getOption('entity'));
        $question->setValidator(array('Sensio\Bundle\GeneratorBundle\Command\Validators', 'validateEntityName'));

        $autocompleter = new EntitiesAutoCompleter($this->getContainer()->get('doctrine')->getManager());
        $autocompleteEntities = $autocompleter->getSuggestions();
        $question->setAutocompleterValues($autocompleteEntities);
        $entity = $originalEntity = $questionHelper->ask($input, $output, $question);

        $input->setOption('entity', $entity);

        list($bundle, $entity) = $this->parseShortcutNotation($entity);

        list(,$controller) = explode(':', $originalEntity);

        $controller .= 's';

        $question = new Question($questionHelper->getQuestion('The Controller name', $controller), $controller);

        $controller = $questionHelper->ask($input, $output, $question);
        $input->setOption('controller', $controller);

        if($input->hasOption('controllerSubNamespace')) {
            $controllerSubNamespace = $input->getOption('controllerSubNamespace');
        }
        $question = new Question($questionHelper->getQuestion('Controller sub namespace', $controllerSubNamespace), $controllerSubNamespace);

        $controllerSubNamespace = $questionHelper->ask($input, $output, $question);

        $input->setOption('controllerSubNamespace', $controllerSubNamespace);

        $entityPrefix = '';
        if($input->hasOption('entityPrefix')) {
            $entityPrefix = $input->getOption('entityPrefix');
        }
        $question = new Question($questionHelper->getQuestion('Entity prefix', $entityPrefix), $entityPrefix);

        $entityPrefix = $questionHelper->ask($input, $output, $question);

        $input->setOption('entityPrefix', $entityPrefix);

        if($controllerSubNamespace) {
            $controllerSubNamespace .= '\\';
        }

        // summary
        $output->writeln(array(
            '',
            $this->getHelper('formatter')->formatBlock('Summary before generation', 'bg=blue;fg=white', true),
            '',
            sprintf("You are going to generate a REST controller for \"<info>%s:%s</info>\"", $bundle, $entity),
            '',
            sprintf("Controller name is \"<info>%s</info>\"", $controllerSubNamespace . $controller),

            sprintf("Entity prefix \"<info>%s</info>\"", $entityPrefix),
            '',
        ));
    }

    protected function getSkeletonDirs(BundleInterface $bundle = null)
    {
        $skeletonDirs = array();

        if (isset($bundle) && is_dir($dir = $bundle->getPath().'/Resources/DrawDrawBundle/skeleton')) {
            $skeletonDirs[] = $dir;
        }

        if (is_dir($dir = $this->getContainer()->get('kernel')->getRootdir().'/Resources/DrawDrawBundle/skeleton')) {
            $skeletonDirs[] = $dir;
        }

        $skeletonDirs[] = __DIR__.'/../Resources/skeleton';
        $skeletonDirs[] = __DIR__.'/../Resources';

        return $skeletonDirs;
    }


    protected function createGenerator()
    {
        return new RestControllerGenerator($this->getContainer()->get('filesystem'));
    }
}