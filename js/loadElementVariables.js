let numBuildings = 0;
let numChecked = 0;
let numUnchecked = 0;
let percentageChecked = 0;
let percentageUnchecked = 0;

const refreshButton = document.getElementById('refreshButton');
const exportButton = document.getElementById('mainExportButton');
const buildingsTable = document.getElementById('buildingsTable');
const editForm = document.getElementById('editForm');
const qrTemplate = document.getElementById('qrTemplate');
const qrForm = document.getElementById('qrForm');
const tableSelectedForm = document.getElementById('tableSelectedForm');
const selectSubmits = document.getElementById('selectSubmits');
const selectedBuildingsText = document.getElementById('selectedBuildings');
const qrContainer = document.getElementById('qrcode');
const qrPreview = document.getElementById('qrcodePreview');
const qrPreviewTitle = document.getElementById('qrPreviewTitle');
const qrPreviewIC = document.getElementById('qrPreviewIC');
let loadAll = document.getElementById('loadAll');
let contractorList = [];
let selectedNames = [];

let resultMessage = document.getElementById('resultMessage');
let changeManagerDiv = document.getElementById('changeManagerDiv');
let changeICDiv = document.getElementById('changeICDiv');
let editAllMessage = document.getElementById('editAllMessage');