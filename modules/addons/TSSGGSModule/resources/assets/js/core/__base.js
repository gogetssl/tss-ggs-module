$('body').attr('id', 'layers2-body');

/*
 * Init app on page loaded (supports ie11+)
 */
const loadApplication = function () {
    new Promise(function (resolve, reject) {
        resolve();
    }).then(function () {
        var appContainers = {
            vueContainerId: document.getElementById(vueContainerId)
        }
        
        ret = mgEventHandler.runCallback('AppsPreLoad', null, {appContainers: appContainers});
        return ret;
    }).then(function () {
        mgPageController = new mgVuePageControler(vueContainerId);
        mgPageController.vinit();
    });
};

const appLoader = function (event) {
    if (document.readyState === "complete" || document.readyState === "interactive")
    {
        document.removeEventListener('readystatechange', appLoader);
        loadApplication();
    }
}

if (document.readyState === "complete")
{
    appLoader({
        readyState: document.readyState
    });
} else
{
    document.addEventListener('readystatechange', appLoader);
}


// if (document.readyState == 'complete')
// {
//     loadApplication();
// } else
// {
//     document.onreadystatechange = function () {
//         if (document.readyState === "interactive")
//         {
//             loadApplication()
//         }
//     };
// }
