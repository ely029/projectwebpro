angular.module('globalLoader', [])
    .config(function ($httpProvider) {
        $httpProvider.interceptors.push('HttpInterceptorLoader');
    })
    .factory('HttpInterceptorLoader', function ($q, dependency1, dependency2) {
        return {
            // optional method
            'request': function (config) {
                angular.element('#ppro-loader').show();
                return config;
            },
            // optional method
            'requestError': function (rejection) {
                angular.element('#ppro-loader').hide();
                return $q.reject(rejection);
            },
            // optional method
            'response': function (response) {
                angular.element('#ppro-loader').hide();
                return response;
            },
            // optional method
            'responseError': function (rejection) {
                angular.element('#ppro-loader').hide();
                return $q.reject(rejection);
            }
        };
    });

var app = angular.module('ProjexApp', ['fcsa-number', 'ui.select', 'ngSanitize', 'angular-loading-bar']);

app.factory('Poller', function($http, $timeout) {
var data = { response: {}, calls: 0 };
    var poller = function() {
        var companyId = angular.element('#companyId').val();
        if(!companyId) {
            return;
        }

        if(data.stop){
            return;
        }

        $http.get('/api/company/'+companyId+'/qb/checkqueue', {
            ignoreLoadingBar: true
          }).then(function(r) {
                data.calls++;

                if(r.data && data.calls > 1){
                    data.stop = true;
                    $http.post('/api/employees/notifyqb',
                    {},
                    {
                        ignoreLoadingBar: true
                    }
                    ).then(function (r) {
                    });
                }
                $timeout(poller, 120000);
        });
    };
    poller();

    return {
        data: data
    };
});

app.run(function(Poller) {});


String.prototype.contains = function (it) {
    return this.indexOf(it) != -1;
};

app.config(function ($interpolateProvider, $sceProvider) {
    $interpolateProvider.startSymbol('{[');
    $interpolateProvider.endSymbol(']}');
    $sceProvider.enabled(false);
});

app.config(['cfpLoadingBarProvider', function (cfpLoadingBarProvider) {
    cfpLoadingBarProvider.spinnerTemplate = '<div id="ppro-loader"></div>';
}]);

app.directive('selectOnBlur', function() {
    return {
        require: 'uiSelect',
        link: function(scope, elm, attrs, ctrl) {
            elm.on('blur', 'input.ui-select-search', function(e) {
                if(ctrl.open && (ctrl.tagging.isActivated || ctrl.activeIndex >= 0)){
                    ctrl.select(ctrl.items[ctrl.activeIndex]);
                }
                e.target.value = '';
            });
        }
    };
})

// property filter
app.filter('propsFilter', function () {
    return function (items, props) {
        var out = [];

        if (angular.isArray(items)) {
            var keys = Object.keys(props);

            items.forEach(function (item) {
                var itemMatches = false;

                for (var i = 0; i < keys.length; i++) {
                    var prop = keys[i];
                    var text = props[prop].toLowerCase();
                    if (item[prop].toString().toLowerCase().indexOf(text) !== -1) {
                        itemMatches = true;
                        break;
                    }
                }

                if (itemMatches) {
                    out.push(item);
                }
            });
        } else {
            // Let the output be the input untouched
            out = items;
        }

        return out;
    };
});

app.directive('modalWindow', function () {
    return {
        restrict: 'EA',
        link: function (scope, element) {
            $(".modal-dialog").draggable({
                handle: ".modal-header",
            });
        }
    };
});

app.directive('keyEnter', function () {
    return function (scope, element, attrs) {
        element.bind("keydown keypress", function (event) {
            if (event.which === 13) {
                scope.$apply(function () {
                    scope.$eval(attrs.keyEnter);
                });
                event.preventDefault();
            }
        });
    };
});

app.directive('format', ['$filter', function ($filter) {
    return {
        require: '?ngModel',
        link: function (scope, elem, attrs, ctrl) {
            if (!ctrl)
                return;

            var format = {
                prefix: '$',
                centsSeparator: '.',
                thousandsSeparator: ',',
                allowNegative: true
            };

            function precisionRound(number, precision) {
                var factor = Math.pow(10, precision);
                return Math.round(number * factor) / factor;
            }

            ctrl.$parsers.unshift(function (value) {
                elem.priceFormat(format);
                if (value == '$-0.0') {
                    elem[0].value = elem[0].value.replace('$-', '$');
                } else if (value == '$-0.00') {
                    elem[0].value = elem[0].value.replace('$-', '$');
                }

                return elem[0].value;
            });

            ctrl.$formatters.unshift(function (value) {
                elem[0].value = precisionRound(ctrl.$modelValue * 100, 2);
                elem.priceFormat(format);
                //                    if(value.contains('-') && !elem[0].value.contains('-')){
                //                        elem[0].value = elem[0].value.replace('$','$-');
                //                    }

                return elem[0].value;
            });
        }
    };
}]);

app.directive('realTimeCurrency', function ($filter, $locale) {
    var decimalSep = $locale.NUMBER_FORMATS.DECIMAL_SEP;
    var toNumberRegex = new RegExp('[^0-9\\' + decimalSep + ']', 'g');
    var trailingZerosRegex = new RegExp('\\' + decimalSep + '0+$');
    var filterFunc = function (value) {
        return $filter('currency')(value);
    };

    function getCaretPosition(input) {
        if (!input)
            return 0;
        if (input.selectionStart !== undefined) {
            return input.selectionStart;
        } else if (document.selection) {
            // Curse you IE
            input.focus();
            var selection = document.selection.createRange();
            selection.moveStart('character', input.value ? -input.value.length : 0);
            return selection.text.length;
        }
        return 0;
    }

    function setCaretPosition(input, pos) {
        if (!input)
            return 0;
        if (input.offsetWidth === 0 || input.offsetHeight === 0) {
            return; // Input's hidden
        }
        if (input.setSelectionRange) {
            input.focus();
            input.setSelectionRange(pos, pos);
        } else if (input.createTextRange) {
            // Curse you IE
            var range = input.createTextRange();
            range.collapse(true);
            range.moveEnd('character', pos);
            range.moveStart('character', pos);
            range.select();
        }
    }

    function toNumber(currencyStr) {
        return parseFloat(currencyStr.replace(toNumberRegex, ''), 10);
    }

    return {
        restrict: 'A',
        require: 'ngModel',
        link: function postLink(scope, elem, attrs, modelCtrl) {
            modelCtrl.$formatters.push(filterFunc);
            modelCtrl.$parsers.push(function (newViewValue) {
                var oldModelValue = modelCtrl.$modelValue;
                var newModelValue = toNumber(newViewValue);
                modelCtrl.$viewValue = filterFunc(newModelValue);
                var pos = getCaretPosition(elem[0]);
                elem.val(modelCtrl.$viewValue);
                var newPos = pos + modelCtrl.$viewValue.length -
                    newViewValue.length;
                if ((oldModelValue === undefined) || isNaN(oldModelValue)) {
                    newPos -= 3;
                }
                setCaretPosition(elem[0], newPos);
                return newModelValue;
            });
        }
    };
});

app.controller('ClassController', function ($scope, $http, $filter) {
    $scope.companyId = angular.element('#companyId').val();
    $scope.newClass = {};
    $scope.newClass.name = '';
    $scope.init = function () {
        $http.get('/api/company/' + $scope.companyId + '/purchaseClassList').then(function (r) {
            $scope.classes = r.data;
            $scope.classes.shift();
        });
    };

    $scope.addPurchaseClass = function () {
        if ($scope.newClass.name == '') {
            toastr.error('Please input a name for the class.');
            return;
        }

        $http.post('/api/company/' + $scope.companyId + '/purchaseClass', $scope.newClass).then(function (r) {
            toastr.info('You have created a new purchase class', 'Add Complete!');
            $scope.newClass.name = '';
            $scope.init();
        });
    };

    $scope.deleteClass = function (item, cannotDelete) {
        if (cannotDelete) {
            toastr.error('Class cannot be deleted because purchases have been made on it.');
            return;
        }
        $http.delete('/api/purchaseclass/' + item.item.id).then(function (r) {
            toastr.info('You have deleted a class', 'Delete Complete!');
            $scope.init();
        });
    };

    $scope.init();
});












app.controller('UserManagementController', function ($scope, $http, $filter, $timeout) {
    var vm = this;
    $scope.companyId = angular.element('#companyId').val();
    $scope.newUser = {};
    $scope.listRoles = [{
        name: "approver"
    }, {
        name: "accountant"
    }, {
        name: "admin"
    }, {
        name: "purchaser",
        checked: true
    }];
    $scope.listDays = [{
        name: "Monday"
    }, {
        name: "Tuesday"
    }, {
        name: "Wednesday"
    }, {
        name: "Thursday"
    }, {
        name: "Friday"
    }];

    $scope.init = function () {
        $http.get('/api/company/' + $scope.companyId + '/employees').then(function (r) {
            $scope.users = r.data;

            $timeout(function () {
                $('.ui-select-container').click(function () {
                    var $this = $(this);
                    $this.find('.ui-select-search').get(0).focus()
                    $this.find('.ui-select-search').get(0).click()
                });
            });
        });
    };

    // $http.get('/api/company/' + $scope.companyId + '/paymentTypesList').then(function (r) {
    //     vm.paymentTypes = r.data;
    // });

    vm.singleDemo = {};
    vm.singleDemo.color = '';
    vm.multipleDemo = {};

    vm.tagSelected = function ($item, $model) {
        $http.post('/api/company/' + $scope.companyId + '/employee/paymentTypes/add', {
            name: $item.name,
            employeeId: $scope.targetEmployee.id
        }).then(function (r) {
            $http.get('/api/company/' + $scope.companyId + '/paymentTypesList').then(function (r) {
                vm.paymentTypes = r.data;
            });
        });
    };

    vm.onRemove = function ($item, $model) {
        $http.post('/api/company/' + $scope.companyId + '/employee/paymentTypes/remove', {
            itemId: $item.id,
            name: $item.name,
            employeeId: $scope.targetEmployee.id
        }).then(function (r) {
            $http.get('/api/company/' + $scope.companyId + '/paymentTypesList').then(function (r) {
                vm.paymentTypes = r.data;
            });
        });
    };

    $scope.removed = function ($item, $model) {
        $http.post('/api/company/' + $scope.companyId + '/employee/paymentTypes/remove', {
            itemId: $item.id,
            name: $item.name,
            employeeId: $scope.targetEmployee.id
        }).then(function (r) {
            $http.get('/api/company/' + $scope.companyId + '/paymentTypesList').then(function (r) {
                vm.paymentTypes = r.data;
            });
        });
    };

    vm.tagTransform = function (newTag) {
        var item = {
            name: newTag
        };

        return item;
    };

    $scope.formatRoleText = function (role) {
        return role.replace("ROLE_", "");;
    };

    $scope.initAddUser = function () {
        vm.multipleDemo.selectedPeople = [];
        $scope.openAddUser();
    };

    $scope.init();

    $scope.addUser = function () {
        $scope.newUser.roles = $scope.getRoles($scope.listRoles);
        $scope.newUser.companyId = angular.element('#companyId').val();
        $scope.newUser.paymentTypes = $scope.getPaymentTypes(vm.multipleDemo.selectedPeople);

        //return;
        $http.post('/api/company/' + $scope.newUser.companyId + '/employees', $scope.newUser).then(function (r) {
            if (r.data.success == true) {
                toastr.info(r.data.message);
                $scope.init();
                $scope.newUser = {};

                $http.get('/api/company/' + $scope.companyId + '/paymentTypesList').then(function (r) {
                    vm.paymentTypes = r.data;
                });

                $http.get('/api/company/' + $scope.companyId + '/employees').then(function (r) {
                    $scope.users = r.data;
                    $scope.listRoles = [{
                        name: "approver"
                    }, {
                        name: "accountant"
                    }, {
                        name: "admin"
                    }, {
                        name: "purchaser",
                        checked: true
                    }];

                    $('#adduserModal').modal('hide');
                    location.reload();
                });

                $http.get('/api/billing/' + $scope.companyId + '/update').then(function (r) {});


            } else {
                toastr.error(r.data.errorMessage, 'Error');
            }
        });
    };
    $scope.openAddUser = function () {
        $scope.newUser.firstName = "";
        $scope.newUser.lastName = "";
        $scope.newUser.email = "";
        $scope.listRoles = [{
            name: "approver"
        }, {
            name: "accountant"
        }, {
            name: "admin"
        }, {
            name: "purchaser",
            checked: true
        }];
        $('#adduserModal').modal('show');
    }

    $scope.getPaymentTypes = function (paymentTypes) {
        var selected = [];
        for (var i in paymentTypes) {
            var r = paymentTypes[i];
            selected.push(r.name);
        }

        return selected;
    };

    $scope.initEditUser = function (u) {
        $scope.listRoles = [{
            name: "approver",
            tmp: 'ROLE_APPROVER'
        }, {
            name: "accountant",
            tmp: 'ROLE_ACCOUNTANT'
        }, {
            name: "admin",
            tmp: 'ROLE_ADMIN'
        }, {
            name: "purchaser",
            checked: true
        }];
        $scope.targetUser = angular.copy(u.user);
        $scope.targetEmployee = angular.copy(u);
        var currentRoles = u.roles;
        vm.multipleDemo.selectedPeople = [];
        for (var i in vm.paymentTypes) {
            var item = vm.paymentTypes[i];
            for (var j in item.employee_payment_types) {
                if (item.employee_payment_types[j].employeeId == $scope.targetEmployee.id) {
                    vm.multipleDemo.selectedPeople.push(item);
                }
            }
        }
        //vm.multipleDemo.selectedPeople = [vm.people[5], vm.people[4]];

        for (var i in currentRoles) {
            var crole = currentRoles[i];

            for (var j in $scope.listRoles) {
                var lrole = $scope.listRoles[j];

                if (lrole.tmp == crole) {
                    lrole.checked = true;
                }
            }
        }

        $('#edituserModal').modal('show');
    };
    $scope.employeeId = $('#employeeId').val();
    $scope.editUser = function () {
        $scope.targetUser.roles = $scope.getRoles($scope.listRoles);

        if ($scope.employeeId == $scope.targetEmployee.id) {
            $('.accountant-link').addClass('invisible');
            $('.approver-link').addClass('invisible');
            $('.admin-link').addClass('invisible');
            for (var i in $scope.targetUser.roles) {
                switch ($scope.targetUser.roles[i]) {
                    case 'accountant':
                        $('.accountant-link').removeClass('invisible');
                        break;
                    case 'approver':
                        $('.approver-link').removeClass('invisible');
                        break;
                    case 'admin':
                        $('.admin-link').removeClass('invisible');
                        break;
                }
            }
        }

        $http.put('/api/employees/' + $scope.targetEmployee.id, $scope.targetUser).then(function (r) {
            $scope.init();
            toastr.success('You have updated this user!');
            $scope.listRoles = [{
                name: "approver"
            }, {
                name: "accountant"
            }, {
                name: "admin"
            }, {
                name: "purchaser",
                checked: true
            }];
            $('#edituserModal').modal('hide');
        });
    };

    $scope.deleteUser = function (id) {
        $http.delete('/api/employees/' + id).then(function (r) {
            $scope.init();
            toastr.success('You have disabled this user!');
        });
    };

    $scope.getRoles = function (roles) {
        var selected = [];
        for (var i in roles) {
            r = roles[i];
            if (r.checked == true) {
                selected.push(r.name);

            }
        }

        return selected;
    };
});

app.controller('CreateCompanyController', function ($scope, $http, $filter, $window) {
    $scope.addCompany = function () {
        $http.post('/api/companies', {
            name: $scope.companyName
        }).then(function (r) {
            $window.location.href = '/dashboard/company/' + r.data;
        });
    };


});
