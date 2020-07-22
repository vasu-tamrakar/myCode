import React, { Component } from 'react';
import { USER_ROUTER_PATH , ROUTER_PATH} from 'service/config.js';
import StatementViewer from 'modules/UserModule/components/utils/StatementViewer';
import jQuery from "jquery";
import DatePicker from "react-datepicker";
import addDays from 'date-fns/addDays'
import moment from 'moment';
import Select from 'react-select-plus';
import { connect } from 'react-redux';
import { getTableParams, handleAddShareholder, toastMessageShow } from 'service/common.js';
import { statmentBankList, viewStatement, addOrUpdateStatmentDetails, deleteStatementTransaction, getCategory, vendorNameListAction } from 'modules/UserModule/store/actions';
import { bindActionCreators } from 'redux';
import ReactTable from 'react-table';
import ConfirmAlert from 'hoc/ConfirmAlert/ConfirmAlert'
import _ from 'lodash';

const inputRef = [];
class AddEditBankStatement extends Component {

    constructor(props, context) {
        super(props, context);
        this.state = {
            statementId: this.props.match.params.id,
            pageSize: 10,
            page: 0,
            filtered: [],
            sorted: [],
            msg: '',
            type: '',
            popUpType: '',
            statementData: { bankname: '', issue_date: '' },
            redirectBack: false,
            PDFViewerShow: false,
            pdfFile: '',
            transactionItems: [{ date: '', description: '', transaction_type: '', vendor_id: '', category_id: '', amount: '', main_balance: '', categoryChanged: 0, vendorChanged: 0 }],
            transaction_types: [
                { value: '', label: 'Select Type' },
                { value: '1', label: 'Credit' },
                { value: '2', label: 'Debit' },
            ],
            bankList: [],
            categoryList: [],
            vendorList: [],
            duplicate: false,
            original_id: '',
            newRow: false,
            vendor_disapproved_cat: {},
            formsubmitStartClick:false,
            colExpanded:false

        }

    }


    colExpanderHandler = () => {
        this.setState({colExpanded:!this.state.colExpanded})
    }
 
    commonSelect(e, fieldName) {
        let statement = this.state.statementData;
        statement[fieldName] = e;
        if (fieldName == 'issue_date') {
            var issueDate = new Date(e);
            statement[fieldName] = moment(issueDate).format('YYYY-MM-DD');
        }
        this.setState({ statement })
    }

    handleRemoveTranasactionItem(obj, e, index, stateName) {
        e.preventDefault();
        var state = {};
        var List = obj.state[stateName];
        state[stateName] = List.filter((s, sidx) => index !== sidx);
        obj.setState(state);
    }

    itemsTransaction(obj, stateName, index, fieldName, value, e) {
        if (e) { e.preventDefault(); }
        if (e != undefined && e.target.pattern) {
            const re = eval(e.target.pattern);
            if (e.target.value != '' && !re.test(e.target.value)) {
                return;
            }
        }
        var state = {};
        var List = obj.state[stateName];
        List[index][fieldName] = value;
        List[index]['categoryChanged'] = 0;
        List[index]['vendorChanged'] = 0;
        state[stateName] = Object.assign([], List);

        if (fieldName == 'date') {
            var myDate = new Date(value);
            List[index]['date'] = moment(myDate).format('YYYY-MM-DD');
            state[stateName] = Object.assign([], List);

        }
        if (fieldName == 'category_id') {
            List[index]['categoryChanged'] = 1;
            state[stateName] = Object.assign([], List);
        }

        if (fieldName == 'vendor_id') {
            List[index]['vendorChanged'] = 1;
            state[stateName] = Object.assign([], List);
        }

        obj.setState(state
            , () => {
                if (stateName == 'transactionItems' && _.includes(['amount', 'description', 'main_balance'], fieldName)) {
                    if (inputRef[fieldName + index] != undefined) {
                        inputRef[fieldName + index].current.focus();
                    }
                }
                if(stateName=='transactionItems'){
                    this.tableValidationcall();
                }
            }
        );
    }

    tableValidationcall=()=>{
        if(this.state.formsubmitStartClick){
            jQuery('.input_valid_tbl').valid();
        }
    }

    moveCaretAtEnd = (e) => {
        var temp_value = e.target.value
        e.target.value = ''
        e.target.value = temp_value
    }


    formSubmitHandler = (e) => {
        e.preventDefault();
        this.setState({formsubmitStartClick:false});
        var stmtandTransData = {};
        let formId = "#editStatement";
        if (this.state.formKey == "add") {
            formId = "#addStatement";
        }
        var validator = jQuery(formId).validate();
        if (jQuery(formId).valid()) {
            stmtandTransData = this.formDataget();

            if (this.state.transactionItems != "") {
                this.props.addOrUpdateStatmentDetails(stmtandTransData).then(json => {
                    if (json.status) {
                        let data = getTableParams(this.state);
                        this.fetchData(data);
                        toastMessageShow(json.msg, 's');
                        setTimeout(() => this.setState({ redirectBack: true }), 2000);
                    } else {
                        if (json.key == 'duplicate') {
                            this.setState({ duplicate: true, original_id: json.original_id });
                            stmtandTransData = this.formDataget();
                            this.requestDuplicateStatement(stmtandTransData);
                        }
                        toastMessageShow(json.msg, 'e');
                    }
                });
            }
            // }
            //this.setState({disabled:false});
        }
        else {
            this.setState({formsubmitStartClick:true},()=>{
                validator.focusInvalid();
                this.tableValidationcall();
            });
        }
    }

    formDataget = () => {
        var stmtandTransData = {};
        if (this.state.formKey == "add") {
            stmtandTransData = {
                'statmentData': { 'statement_for_of': this.state.statementData.bankname, 'issue_date': this.state.statementData.issue_date },
                'transactionData': this.state.transactionItems,
                'statement_id': '',
                'duplicate': this.state.duplicate,
                'original_id': this.state.original_id
            }
        } else if (this.state.formKey == "edit") {
            stmtandTransData = {
                'statmentData': { 'statement_for_of': this.state.statementData.bankname, 'issue_date': this.state.statementData.issue_date },
                'transactionData': this.state.transactionItems,
                'statement_id': this.state.statementId,
                'duplicate': this.state.duplicate,
                'original_id': this.state.original_id
            }
        }
        return stmtandTransData;
    }

    requestDuplicateStatement = (data) => {
        ConfirmAlert({}, 'Are you sure you want to  make duplicate this line item ?', '', { heading_title: 'Duplicate Statement' }).then((result) => {
            if (result.status) {
                var statementId = { 'statement_id': this.state.statementId };
                this.props.addOrUpdateStatmentDetails(data).then(json => {
                    if (json.status) {
                        let data = getTableParams(this.state);
                        this.fetchData(data);
                        toastMessageShow(json.msg, 's');
                        setTimeout(() => this.setState({ redirectBack: true }), 2000);
                    }
                });
            } else {
                this.setState({ duplicate: false, original_id: '' });
            }
        })
    }

    cancelFormHandler = (e) => {
        e.preventDefault();
        this.props.history.push(USER_ROUTER_PATH + 'bank_statement');
    }

    deleteRowHandler = (key) => {
        this.setState({ loading: true })
        let deleteData = {
            'statement_id': this.state.statementId,
            'transaction_id': key
        }
        this.props.deleteStatementTransaction(deleteData).then(json => {
            if (json.status) {
                let data = getTableParams(this.state);
                this.fetchData(data);
                toastMessageShow(json.msg, 's');
                setTimeout(() => this.setState({ redirect: true }), 2000);
            }
        });
    }

    addrowdata() {

        let list = this.state.transactionItems;
        let aa = { date: '', description: '', transaction_type: '', vendor_id: '', category_id: '', amount: '', main_balance: '', categoryChanged: 0, vendorChanged: 0 };
        list.push(aa);
        let count = list.length - 1;
        this.createRefData('description' + count);
        this.createRefData('amount' + count);
        this.createRefData('main_balance' + count);
        this.setState({ transactionItems: list, });

        this.setState({transactionItems:list},()=>{
            this.tableValidationcall();
        });
    }

    deleterow(e, i) {
        let list = this.state.transactionItems;
        var index = e;

        if (list.length > 1) {
            list.splice(i, 1);
            this.setState({ transactionItems: list},()=>{
                this.tableValidationcall();
            });
        }
    }

    fetchData = (vals, instance) => {
        this.setState({ loading: true })
        let data = {
            pageSize: vals.pageSize, page: vals.page, sorted: vals.sorted, filtered: vals.filtered,
            statement_id: (this.state.statementId) ? this.state.statementId : '',
            formType: (this.state.formKey) ? this.state.formKey : ''
        }
        this.props.viewStatement(data)
            .then(res => {
                if (res.status) {
                    var resultData = res.data;
                   
                    this.setState({
                        pages: resultData.pages,
                        all_count: resultData.all_count,
                        loading: false,
                        statementData: resultData.statement_data,
                        userData: resultData.user_data,
                        transactionItems: resultData.statement_transaction,
                        vendor_disapproved_cat: resultData.vendor_disapproved_cat,
                        pdfFile: resultData.statement_pdf_file,
                        page:resultData.pages < vals.page + 1 ? 0 :vals.page 
                    }, () => {
                        if (this.state.transactionItems.length > 0) {
                            this.state.transactionItems.map((r, index) => {
                                this.createRefData('description' + index);
                                this.createRefData('amount' + index);
                                this.createRefData('main_balance' + index);
                                //  this.createRefData('category_id' + index);

                            });
                        }
                    })
                }
                this.setState({ loading: false })
            })
    }


    createRefData = (index) => {
        inputRef[index] = React.createRef();
    }


    componentWillMount() {
        if (this.props.type == "add") {


            this.setState({
                formKey: "add",
                editKey: 0
            }, () => {
                if (this.state.transactionItems.length > 0) {
                    this.state.transactionItems.map((r, index) => {
                        this.createRefData('description' + index);
                        this.createRefData('amount' + index);
                        this.createRefData('main_balance' + index);
                    });
                }
            })
        } else {
            this.props.getCategory().then((res) => {
                if (res.status) {
                    this.setState({ categoryList: res.data });
                }
            });

            this.props.vendorNameListAction()
                .then((result) => {
                    if (result.status) {
                        this.setState({ vendorList: result.data });
                    }
                });

            this.setState({
                formKey: "edit",
                statement: this.props.SingleBankStatementData,
                transactionItems: []
            }, () => {
                if (this.state.transactionItems.length > 0) {
                    this.state.transactionItems.map((r, index) => {
                        this.createRefData('description' + index);
                        this.createRefData('amount' + index);
                        this.createRefData('main_balance' + index);
                    });
                }
            })
        }
        this.props.statmentBankList()
            .then((result) => {
                if (result.status) {
                    this.setState({ bankList: result.data });
                }
            });
    }




    render() {
        let onlyStatement = this.props.SingleStatementData;
        if (this.state.redirectBack) {
            this.props.history.push(USER_ROUTER_PATH + "bank_statement");
        }
        const tblColumn = [
            { Header: 'Sr.', accessor: 'id', maxWidth: 60, Cell: (props) => <div>{props.index + 1}</div> },
            {
                Header: "Date",
                accessor: 'transaction_date',
                maxWidth: 150,
                Cell: (props) => {
                    let i = props.index;
                    let val = props.original;
                    return (
                        <div className={'w-100'}>
                            <DatePicker

                                className="tbl_inp_field input_valid_tbl"
                                selected={
                                    val.date ?
                                        moment(val.date).toDate() : null
                                }
                                onChange={(e) => this.itemsTransaction(this, 'transactionItems', i, 'date', e)}
                                dateFormat="dd/MM/yyyy"
                                name={"date" + i}
                                id={"date" + i}
                                required={true}

                                //minDate={addDays(new Date(), 0)}
                                maxDate={moment(this.state.statementData.issue_date).toDate()}
                                placeholderText={'dd/mm/yyyy'}
                                title={''}
                                popperPlacement={"right-start"}
                                popperModifiers={{
                                    flip: {
                                        behavior: ["right-start"] // don't allow it to flip to be above
                                    },
                                    preventOverflow: {
                                        enabled: false // tell it not to try to stay within the view (this prevents the popper from covering the element you clicked)
                                    },
                                    hide: {
                                        enabled: false // turn off since needs preventOverflow to be enabled
                                    }
                                }}
                            />
                        </div>
                    )
                }
                // <div>{(props.original.transaction_date!='0000-00-00')?props.original.transaction_date:''}</div>
            },
            {
                Header: 'Description',
                accessor: 'description',
                Cell: (props) => {
                    let i = props.index;
                    let val = props.original;
                    return (
                        <div className={'w-100'}>
                            <textarea
                                className="tbl_inp_field input_valid_tbl"
                                name={"description" + i}
                                id={"description" + i}
                                style={{ resize: 'none' }}
                                data-rule-required={true}
                                data-msg-required={'Please enter description'}
                                value={val.description || ''}
                                ref={inputRef['description' + i]}
                                onChange={(e) => this.itemsTransaction(this, 'transactionItems', i, 'description', e.target.value, e)}
                                onFocus={(e) => this.moveCaretAtEnd(e)}
                                maxLength={100}
                                minLength={2}
                                placeholder={'Item Description'}
                            ></textarea>
                        </div>
                    )

                }
            },
            {
                Header: "Vendor",
                accessor: 'vendor_id',
                show: (this.state.formKey == 'edit') ? true : false,

                Cell: (props) =>{ return (<div className={'w-100'}>
                    <select className={'tbl_inp_field input_valid_tbl'} id={'vendor_id' + props.index}
                        name={'vendor_id' + props.index}
                        value={props.original.vendor_id}
                        placeholder="Select Vendor"
                        onChange={(e) => this.itemsTransaction(this, 'transactionItems', props.index, 'vendor_id', e.target.value, e)}
                        disabled={(props.original.vendor_id == 0 || props.original.read_status == 1) ? true : false}
                        data-rule-required={true}>
                        <option value="">Select Vendor</option>
                        {this.state.vendorList.length > 0 ?

                            this.state.vendorList.map((val) => {
                                return <option value={val.value} key={(props.index + 1) + val.value}>{val.label}</option>;
                            })
                            : ''}
                    </select>
                </div>)}
            },
            {
                Header: "Category",
                accessor: 'category_id',
                show: (this.state.formKey == 'edit') ? true : false,

                Cell: props => <div className={'w-100'}>

                    <select
                        className={'tbl_inp_field input_valid_tbl'}
                        id={'category_id' + props.index}
                        name={'category_id' + props.index}
                        value={props.original.category_id}
                        disabled={(props.original.category_id == 0 || props.original.read_status == 1) ? true : false}
                        onChange={(e) => this.itemsTransaction(this, 'transactionItems', props.index, 'category_id', e.target.value, e)}
                        data-rule-required={true}>
                        <option value="">Select Category</option>
                        {this.state.categoryList.length > 0 ?
                            this.state.categoryList.map((val) => {
                                let disapprovedCat = _.split((this.state.vendor_disapproved_cat.hasOwnProperty(props.original.vendor_id) && this.state.vendor_disapproved_cat[props.original.vendor_id].hasOwnProperty('diapproved_cat') ? this.state.vendor_disapproved_cat[props.original.vendor_id]['diapproved_cat'] : ''), ',');
                                if (_.includes(disapprovedCat, val.value)) {
                                    return <React.Fragment />;
                                } else {
                                    return (<option value={val.value} key={(props.index + 1) + val.value} >{val.label}</option>);
                                }
                            })
                            : ''}
                    </select>
                </div>
            },

            {
                Header: "Debit/Credit",
                accessor: 'transaction_type',
                maxWidth: 150,
                Cell: props => <div className={'w-100'}>
                    <select
                        className={'tbl_inp_field input_valid_tbl'}
                        required={true}
                        id={'transaction_type' + props.index}
                        name={'transaction_type' + props.index}
                        value={props.original.transaction_type}
                        onChange={(e) => this.itemsTransaction(this, 'transactionItems', props.index, 'transaction_type', e.target.value, e)}
                        data-rule-required={true}>
                        {this.state.transaction_types.length > 0 ?
                            this.state.transaction_types.map((val) => {
                                return <option value={val.value} key={(props.index + 1) + val.value}>{val.label}</option>;
                            })
                            : ''}
                    </select>
                </div>
            },
            {
                Header: 'Amount',
                accessor: 'amount',
                maxWidth: 150,
                Cell: (props) => {
                    let i = props.index;
                    let val = props.original;
                    return (<div className={'w-100'}>
                        <input
                            type="text"
                            className="tbl_inp_field input_valid_tbl"
                            onChange={(e) => this.itemsTransaction(this, 'transactionItems', i, 'amount', e.target.value, e)}
                            value={val.amount || ''}
                            name={"amount" + i}
                            id={"amount" + i}
                            data-rule-required="true"
                            data-msg-required={"Please enter Amount"}
                            data-rule-number={true}
                            data-rule-amt_valid={true}
                            maxLength={11}
                            minLength={1}
                            min={0}
                            data-msg-min={'Please enter a positive value greater than 0.'}
                            ref={inputRef['amount' + i]}
                            placeholder={'Amount'}
                        /></div>)
                }
            },
            {
                Header: 'Balance',
                accessor: 'main_balance',
                maxWidth: 150,
                Cell: (props) => {
                    let i = props.index;
                    let val = props.original;
                    return (<div className={'w-100'}>
                        <input
                            type="text"
                            className="tbl_inp_field input_valid_tbl"
                            onChange={(e) => this.itemsTransaction(this, 'transactionItems', i, 'main_balance', e.target.value, e)}
                            value={val.main_balance || ''}
                            name={"main_balance" + i}
                            id={"main_balance" + i}
                            data-rule-required="true"
                            data-msg-required={"Please enter balance."}
                            data-rule-number={true}
                            data-rule-valid_balance={true}
                            maxLength={12}
                            minLength={1}
                            placeholder={'Balance'}
                            ref={inputRef['main_balance' + i]}
                        /></div>)
                }
            },
            {
                Header: 'Action',
                width: 120,
                Cell: (props) => {

                    let i = props.index;
                    let val = props.original;
                    var count = this.state.transactionItems.length;
                    return (<div className="action_c w-100 text-left" >
                        {count > 1 ?
                            <i className="fa fa-minus delete_ic" onClick={(e) => this.deleterow(e, i)}></i>
                            : ''}
                        {count == (i + 1) ?
                            <i className="fa fa-plus edit_ic" onClick={() => this.addrowdata()}></i>
                            : ''}
                    </div>);
                }
            },
        ]

        let filePath = (this.state.statementData) ? this.state.statementData.user_id + '/' + this.state.statementData.statement_file_path : '';
        let sourceType = (this.state.statementData) ? this.state.statementData.source_type : '';
        sourceType = sourceType == '' || sourceType == undefined ? 2 : sourceType;
        return (
            <React.Fragment>
                <section className="edit_invSec">
                    <div className="cs_wrapper">
                        <div className="hdngPrt_2__">
                            {this.state.formKey == "add" ?
                                <h3>Add Statement</h3>
                                :
                                <h3>Edit Statement:- <span>{this.state.statementId}</span></h3>
                            }
                        </div>
                        <form id={this.state.formKey == "add" ? 'addStatement' : 'editStatement'}>
                            <div className="row mt-4">



                                

                                {/* Statement Viewer*/}
                                <div className={"col-12 mb-3 " + (sourceType == "2"? "": this.state.colExpanded? " col-md-10":" col-md-7")}>

                                    <div className="row ">

                                        <div className="col-lg-4 col-md-6 col-12">
                                            <div className="form-group mb-4">
                                                <label>Statement Of</label>

                                                <div className="cstm_select">

                                                    <Select
                                                        options={this.state.bankList}
                                                        name="bankname"
                                                        simpleValue={true}
                                                        required={true}
                                                        searchable={true}
                                                        clearable={false}
                                                        placeholder="Please Select Bank"
                                                        className={' cstm_select'}
                                                        data-rule-required="true"
                                                        value={(this.state.statementId) ? this.state.statementData.bankname : this.state.statementData.bankname}
                                                        onChange={(e) => this.commonSelect(e, 'bankname')}
                                                        data-msg-required={"Please Select Bank"}
                                                        inputRenderer={(props) => <input {...props} name="bankname" data-msg-required={'Please select Bank.'} />}
                                                      //  disabled={(this.state.formKey == 'edit') ? true : false}

                                                    />
                                                </div>
                                                <div>
                                                    <label className={'error'} htmlFor={'bankname'}></label>
                                                </div>

                                            </div>

                                        </div>
                                        <div className="col-lg-4 col-md-6 col-12">
                                            <div className="form-group mb-4">
                                                <label>Statement Generate Date</label>
                                                <div className="addon_field calendar_addOn mb-3">
                                                    <DatePicker
                                                        className="form-control custom_input2"
                                                        selected={
                                                            this.state.statementData.issue_date ?
                                                                moment(this.state.statementData.issue_date).toDate()  : null
                                                        }
                                                        onChange={(e) => this.commonSelect(e, 'issue_date')}
                                                        dateFormat="dd/MM/yyyy"
                                                        name="issue_date"
                                                        id="issue_date"
                                                        required={true}
                                                        //readOnly={(this.state.statementId) ? true : false}
                                                        maxDate={addDays(new Date(), 0)}
                                                        title={''}
                                                    />
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                    <hr className="mb-4 mt-3" />
                                    <h4 className="mb-3 mt-5"><strong>Transaction Details</strong></h4>
                                    <div className="row mb-3">
                                        <div className="col-12">
                                            <div className={"data_table_cmn text-center item_table statement_tbl__ " + (sourceType == "2"? " ":" tbl_ltd_h")}>
                                                <ReactTable
                                                    data={this.state.transactionItems}
                                                    defaultPageSize={50}
                                                    minRows={0}
                                                    previousText={''}
                                                    nextText={''}
                                                    columns={tblColumn}
                                                    manual
                                                    onFetchData={this.fetchData}
                                                    pages={this.state.pages}
                                                    page={this.state.page}
                                                    filtered={this.state.filtered}
                                                    loading={this.state.loading}
                                                    statementId={this.state.statementId}
                                                    showPagination={false}
                                                    ref={this.reactTable}
                                                />
                                            </div>
                                        </div>
                                    </div>
                                    <div className="row">
                                        <div className="col-lg-4 col-md-4 col-12">
                                            <div className="row">
                                                <div className="col-6 pr-1">
                                                    <input type="button" className="btn btn-block cmn-btn1 c_pointer" disabled={this.state.disable} onClick={this.formSubmitHandler} value={'Submit'} />
                                                </div>
                                                <div className="col-6 pl-1">
                                                    <input type="button" className="btn btn-block cmn-btn2 c_pointer" onClick={this.cancelFormHandler} value={'CANCEL'} />
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    {(sourceType != "2") ?
                                    <div className={'expandedBtnAreea'}>
                                        <i onClick={this.colExpanderHandler} className={'fa fa-angle-' + (this.state.colExpanded ? 'left':'right')}></i>
                                    </div>:""}
                                </div>
                            
                            
                                <div className={"col-12 pl-5 " + (sourceType == "2"? "" :this.state.colExpanded? " col-md-2":" col-md-5")}>
                                    {(sourceType != "2") ?
                                        <React.Fragment>
                                            {
                                             <StatementViewer
                                                    file={this.state.pdfFile}
                                                //this.state.pdfFile
                                                /> 
                                            }
                                        </React.Fragment> : ''}
                                </div>
                            
                            </div>
                        </form>
                    </div>
                </section>

            </React.Fragment>
        );

    }
}
const mapStateToProps = state => {
    return {

    };
};

const mapDispatchToProps = (dispatch) => bindActionCreators({
    statmentBankList, viewStatement, addOrUpdateStatmentDetails, deleteStatementTransaction, getCategory, vendorNameListAction
}, dispatch);

export default connect(mapStateToProps, mapDispatchToProps)(AddEditBankStatement);