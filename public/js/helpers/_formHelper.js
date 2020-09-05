/**
 * addNewFieldWithData Add new CollectionType input field with given data
 * @param $prototypeHandler     Prototype field handler 
 * @param $collectionWrapper    Collection field wrapper 
 * @param data                  String with data 
 */
export function addNewFieldWithData($prototypeHandler, $collectionWrapper, data) {
    let newField = $prototypeHandler.data('prototype');
    let counter = $prototypeHandler.data('counter');
    
    newField = newField.replace(/__name__/g, counter);
    $collectionWrapper.append(newField);
    $collectionWrapper.children("input:last-child").val(data);

    $prototypeHandler.data('counter', counter+1);
}

