<?php

namespace App\Console\Commands;

use App\Application\Model\Item;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Console\GeneratorCommand;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;
use Symfony\Component\Console\Input\InputOption;

class MakeAdminModel extends GeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'make:admin_model';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command will generate model , controller , view , migration , datatable class for you';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Model';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function fire()
    {
            $this->addlanguageFile();
            $this->ImportMenuTable();
            $this->createModel();
            $this->createDataTable();
            $this->createController();
            $this->createViews();
            $this->appendRoutes();
            $this->createMigration();

//           $this->addItemtoMenue();
    }


    protected function addItemtoMenue(){
        $name = strtolower($this->getNameInput());
        $path = $this->getPath('Application\\views\\admin\\layout\\menu.blade');
        $this->line('Done append item  to menu file at Application  .');
        $this->files->append($path, $this->buildMenu( $name  , __DIR__.'/stub/menu.stub'));
    }

    protected function ImportMenuTable(){
        $name = $this->getNameInput();
        $order = Item::count();
        $menu = new Item();
        $menu->name = encodeJson(['en' => $name , 'ar' => $name]);
        $menu->link  = '/admin/'.strtolower($name);
        $menu->parent_id  = 0;
        $menu->menu_id  = 1;
        $menu->order = $order+1;
        $menu->type = '';
        $menu->save();
        $this->line('Done Add Item to menu table  .');
    }

    protected function buildMenu($name  , $stub ){
        $stub = $this->files->get($stub);
        return $this->replace( $stub, 'DummyModel',$name)
            ->replaceView( $stub, 'DummyNameBigs',ucfirst($name));
    }

    protected function appendRoutes(){
        $name = strtolower($this->getNameInput());
        $path = $this->getPath('Application\\routes\\admin');
        $this->line('Done append routes to route file at Application route  admin .');
        $this->files->append($path, $this->buildRoute( $name  , __DIR__.'/stub/routes.stub'));
    }

    protected function buildRoute($name  , $stub ){
        $stub = $this->files->get($stub);
        return $this->replace( $stub, 'DummyRoute',$name)
            ->replaceView( $stub, 'DummyView',ucfirst($name));
    }


    protected function createMigration()
    {
        $table = Str::plural(Str::snake(class_basename($this->argument('name'))));
        $this->call('make:migration', [
            'name' => "create_{$table}_table"
        ]);
    }

    protected function createDataTable()
    {
        $name = strtolower($this->getNameInput());
        $path = $this->getPath('Application\\DataTables\\'.$this->getNameInput().'sDataTable');
        $nameDatatable = $this->getNameInput().'sDataTable';
        $this->line('Done create Datatable class  at Application DataTables  '.$this->getNameInput() .'sDatatable .');
        $this->files->put($path, $this->buildDataTable( $name ,  $nameDatatable  , __DIR__.'/stub/datatable.stub'));
    }


    protected function addlanguageFile(){
        $name = strtolower($this->getNameInput());
        $locales  = LaravelLocalization::getSupportedLocales();
        foreach($locales as $key => $locale){
            $this->line('Create  '.$locale['name'] .' Language file .');
            $path = base_path('resources/lang/'.$key.'/'.$name.'.php');
            $this->files->put($path , $this->buildlang($name  , __DIR__.'/stub/lang.stub'));
        }
       return 'Done';
    }


    protected function buildlang($name   , $stub ){
        $stub = $this->files->get($stub);
        return $this->replaceView( $stub, 'DUMMYKEY',$name);
    }


    protected function buildDataTable($name  , $nameDatatable  , $stub ){
        $stub = $this->files->get($stub);
        return $this->replace( $stub, 'DummyDatatable',$nameDatatable)
            ->replace( $stub, 'DummyModelSmall',strtolower($name))
            ->replaceView( $stub, 'DummyModel',ucfirst($name));
    }

    protected function createModel  (){
        $name = $this->qualifyClass($this->getNameInput());
        $path = $this->getPath('Application\\Model\\'.$this->getNameInput());
        $this->line('Done create Model  at Application Model  '.$this->getNameInput() .' .');
        $this->files->put($path, $this->buildClass($name));
    }

    protected function createController()
    {
        $name = $this->qualifyClass(strtolower($this->getNameInput()));
        $controllerName = $this->getNameInput().'Controller';
        $dataTableName = $this->getNameInput().'sDataTable';
        $modelName= $this->getNameInput();
        $viewName = strtolower($this->getNameInput());
        $path = $this->getPath('Application\\Controllers\\Admin\\'.$this->getNameInput().'Controller');
        $this->line('Done create Controller  at Application controller admin '.$this->getNameInput() .'Controller .');
        $this->files->put($path, $this->buildClassController( $name , $controllerName , $dataTableName , $modelName , $viewName, __DIR__.'/stub/controller.stub'));

    }

    protected function getStub()
    {
        return __DIR__.'/stub/model.stub';
    }

    protected function buildClass($name){
        $stub = $this->files->get($this->getStub());
        return $this->replaceNamespace($stub, $name)->replaceClass($stub, $name);
    }

    protected function buildClassController($name ,$controllerName ,  $dataTableName , $modelName , $viewName, $stub){
        $stub = $this->files->get($stub);
        return $this->replace( $stub, 'DummyModel',$modelName)
                    ->replace( $stub,'DummyDataTable' ,  $dataTableName)
                     ->replace( $stub,'DummyView' ,  $viewName)
                     ->replaceNamespace($stub, $name)
                     ->replaceClass($stub, $controllerName);
    }

    protected function getDefaultNamespace($rootNamespace)
    {
        return 'App';
    }

    protected function replace(&$stub,$rep ,  $name)
    {
        $stub = str_replace(
            [$rep],
            $name,
            $stub
        );

        return $this;
    }

    protected function createViews(){
        $path = app_path().'/Application/views/admin/' . strtolower($this->getNameInput());
        $pathButton = app_path().'/Application/views/admin/' . strtolower($this->getNameInput()).'/buttons';

        if(!file_exists($path)){
            File::makeDirectory($path, $mode = 0777, true, true);
        }
        if(!file_exists($pathButton)){
            File::makeDirectory($pathButton, $mode = 0777, true, true);
        }
        $this->CreateOnView('index');
        $this->CreateOnView('edit');
        $this->CreateOnView('show');
        $this->CreateButton('edit');
        $this->CreateButton('delete');
        $this->CreateButton('view');
        $this->CreateButton('langcol');
    }

    protected function CreateOnView($view ){
        $name = strtolower($this->getNameInput());
        $path = $this->getPath('Application\\views\\admin\\'.strtolower($this->getNameInput()).'\\'.$view.'.blade');
        $this->line('Done create view at Application view admin .');
        $this->files->put($path, $this->buildView( $name , __DIR__.'/stub/adminViews/'.$view.'.stub'));
    }

    protected function CreateButton($view ){
        $name = strtolower($this->getNameInput());
        $path = $this->getPath('Application\\views\\admin\\'.strtolower($this->getNameInput()).'\\buttons\\'.$view.'.blade');
        $this->line('Done create action button view at Application view admin '.$this->getNameInput() .' button');
        $this->files->put($path, $this->buildView( $name , __DIR__.'/stub/adminViews/buttons/'.$view.'.stub'));
    }

    protected function buildView($name  , $stub ){
        $stub = $this->files->get($stub);
        return $this->replaceView( $stub, 'DummyView',$name);
    }

    protected function replaceView(&$stub,$rep ,  $name)
    {
        $stub = str_replace(
            [$rep],
            $name,
            $stub
        );
        return $stub;
    }

}
