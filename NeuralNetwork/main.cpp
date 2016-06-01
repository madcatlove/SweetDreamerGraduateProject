
/**
    Simple artifical neural network.
    고전 인공신경망 모델 + back propagation algorithm
    @Author : 이석준(kr.madcat@gmail.com)
    @Date : 2015. 09. 11
 */
#include <iostream>
#include <stdio.h>
#include <vector>
#include <algorithm>
#include <cmath>
#include <cstdlib>
#include <ctime>
#include <time.h>

using namespace std;


//sigmoid 함수 정의
#define sigmoid(x) (1.0/(1.0+exp(-(x))))

class Network
{
    
    
    /*
     다음의 인공신경망은 노드(=뉴런)과 엣지의 집합으로 구성되어 있습니다.
     각 노드는 출력값을 가지며, 모든 엣지는 -1과 1 사이의 가중치 값이 부여됩니다.
     또한 각 노드는 특정 레이어(층)에 속해있는데,
     은닉층(hidden layer)가 n개라고 가정했을 때,
     1번 층을 입력층(input layer)으로 사용하고
     2~n+1 층을 은닉층(hidden layer)로 사용하여
     n+2 층을 출력층(output layer)로 넘버링하여 사용합니다.
     각 층에는 노드(뉴런)들이 포함되어 있는데, 노드들도 각 층마다 1번부터 k번까지 넘버링하여 사용합니다
     */
    
    
    
    
public:
    int numOfInputLayer; //인풋 레이어의 갯수 = 1
    int numOfInputLayerNode; //인풋 레이어의 노드 갯수
    int numOfHiddenLayer; //히든 레이어의 갯수
    int numOfHiddenLayerNode; //히든 레이어의 노드 갯수
    int numOfOutputLayer; //아웃풋 레이어의 갯수 = 1
    int numOfOutputLayerNode; //아웃풋 레이어의 노드 갯수
    int numOfLayer; //레이어 갯수
    int maxNumOfNode; //노드 갯수의 최대값
    double learningRate; //학습률
    
    
private:
    // weight[i][j][k] = i번 레이어의 j번 노드와 그 다음 레이어의 k번 노드를 잇는 연결 가중치
    vector< vector< vector<double> > >weight;
    
    // nodeOutput[i][j] = i번 레이어 j번 노드의 출력값
    public :
    vector< vector<double> > nodeOutput;
    
private:
    // outputError[i][j] = i번 레이어 j번 노드의 기댓값과 출력 사이의 에러값
    vector< vector<double> >outputError;
    
    // threshold[i][j] = i번 레이어 j번 노드의 임계값
    vector< vector<double> >threshold;
    
    
    
    
    
    /*
     입력층의 노드 갯수
     은닉층 갯수
     각 은닉층의 노드 갯수
     출력층의 노드 갯수
     학습률 세팅
     */
public: Network(int numOfInputLayerNode, int numOfHiddenLayer, int numOfHiddenLayerNode,
                int numOfOutputLayerNode, double learningRate)
    {
        this->numOfInputLayerNode = numOfInputLayerNode;
        this->numOfHiddenLayer = numOfHiddenLayer;
        this->numOfHiddenLayerNode = numOfHiddenLayerNode;
        this->numOfOutputLayerNode = numOfOutputLayerNode;
        this->learningRate = learningRate;
        
        this->numOfInputLayer = 1;
        this->numOfOutputLayer = 1;
        this->numOfLayer = numOfInputLayer + numOfHiddenLayer + numOfOutputLayer;
        
        //각 층의 노드 갯수 중 최대값 저장
        maxNumOfNode = max(numOfInputLayerNode,numOfHiddenLayerNode);
        maxNumOfNode = max(maxNumOfNode,numOfOutputLayerNode);
        
    }
    
    
    /*
     인공신경망 세팅
     */
    void init()
    {
        weightInit();
        nodeOutputInit();
        thresholdInit();
        outputErrorInit();
    }
    
    
    /*
     가중치값 세팅
     */
    void weightInit()
    {
        weight.resize(numOfLayer+1);
        for(int i=1;i<weight.size();i++)
        {
            weight[i].resize(maxNumOfNode+1);
            
            for(int j=1;j<weight[i].size();j++)
                weight[i][j].resize(maxNumOfNode+1,(double(rand())/RAND_MAX)*2-1);
        }
    }
    
    
    /*
     노드 출력 세팅
     */
    void nodeOutputInit()
    {
        nodeOutput.resize(numOfLayer+1);
        
        //입력층 세팅
        nodeOutput[1].resize(numOfInputLayerNode+1);
        
        //은닉층 세팅
        for(int i=2;i<=numOfHiddenLayer+1;i++)
            nodeOutput[i].resize(maxNumOfNode+1);
        
        //출력층 세팅
        nodeOutput[numOfLayer].resize(numOfOutputLayerNode+1);
    }
    
    
    /*
     임계값 세팅
     */
    void thresholdInit()
    {
        threshold.resize(numOfLayer+1);
        
        threshold[1].resize(numOfInputLayerNode+1,(double(rand())/RAND_MAX)-0.5);
        
        for(int i=2;i<=numOfHiddenLayer+1;i++)
            threshold[i].resize(maxNumOfNode+1,(double(rand())/RAND_MAX)-0.5);
        
        threshold[numOfLayer].resize(numOfOutputLayerNode+1,
                                     (double(rand())/RAND_MAX)-0.5);
    }
    
    
    /*
     출력 오차 세팅
     */
    void outputErrorInit()
    {
        outputError.resize(numOfLayer+1);
        
        
        //입력층 세팅
        outputError[1].resize(numOfInputLayerNode+1);
        
        //은닉층 세팅
        for(int i=2;i<=numOfHiddenLayer+1;i++)
            outputError[i].resize(maxNumOfNode+1);
        
        //출력층 세팅
        outputError[numOfLayer].resize(numOfOutputLayerNode+1);
    }
    
    
    /*
     feedforward 알고리즘을 기반으로
     각 뉴런의 출력을 계산하여 nodeOutput 벡터에 저장한다
     */
    void activate(vector<double> input)
    {
        //입력층 할당
        for(int i=1;i<=numOfInputLayerNode;i++)
            nodeOutput[1][i] = input[i];
        
        //은닉층 출력 계산
        for(int i=2;i<=numOfHiddenLayer+1;i++)
        {
            for(int j=1;j<=numOfHiddenLayerNode;j++)
            {
                //i번 층의 j번 뉴런의 출력 계산
                nodeOutput[i][j] = 0;
                
                for(int k=1;k<nodeOutput[i-1].size();k++)
                    nodeOutput[i][j] += nodeOutput[i-1][k] * weight[i-1][k][j];
                
                nodeOutput[i][j] -= threshold[i][j];
                nodeOutput[i][j] = sigmoid(nodeOutput[i][j]);
                
            }
        }
        
        //출력층 출력 계산
        for(int j=1;j<=numOfOutputLayerNode;j++)
        {
            nodeOutput[numOfLayer][j]=0;
            
            for(int k=1;k<nodeOutput[numOfLayer-1].size();k++)
                nodeOutput[numOfLayer][j] += nodeOutput[numOfLayer-1][k] * weight[numOfLayer-1][k][j];
            
            nodeOutput[numOfLayer][j] = sigmoid(nodeOutput[numOfLayer][j]);
            
        }
    }
    
    
    
    /*
     입력에 대응하는 결과 기댓값을 받아
     backpropagation 알고리즘을 기반으로
     오차를 계산하여 가중치를 보정한다
     */
    void backPropagation(vector<double> input,vector<double> expected)
    {
        //파라미터 유효성 검증
        if(expected.size()!=numOfOutputLayerNode+1)
            printf("output error\n");
        
        if(input.size()!=numOfInputLayerNode+1)
            printf("input error\n");
        
        
        //각 뉴런의 출력 계산
        activate(input);
        
        
        //backpropagation 으로 오류 역전
        //출력층 오차계산
        for(int k=1;k<=numOfOutputLayerNode;k++)
        {
            outputError[numOfLayer][k] = (expected[k]-nodeOutput[numOfLayer][k])*nodeOutput[numOfLayer][k]*(1-nodeOutput[numOfLayer][k]);
            
            
            //출력층 가중치 보정
            for(int j=1;j<=numOfHiddenLayerNode;j++)
            {
                weight[numOfLayer-1][j][k] += learningRate * outputError[numOfLayer][k] *nodeOutput[numOfLayer-1][j];
            }
        }
        
        
        //은닉층 오차계산
        for(int i=numOfLayer-1;i>=2;i--)
        {
            for(int j=1;j<=numOfHiddenLayerNode;j++)
            {
                //i번 층의 j번 뉴런의 출력오차
                outputError[i][j] = nodeOutput[i][j] * (1-nodeOutput[i][j]);
                
                double prevError = 0;
                
                
                //출력층의 출력오차계산
                if(i==numOfLayer-1)
                {
                    for(int k=1;k<=numOfOutputLayerNode;k++)
                        prevError += outputError[i+1][k] * weight[i][j][k];
                }
                
                //은닉층의 출력오차계산
                else
                {
                    for(int k=1;k<=numOfHiddenLayerNode;k++)
                        prevError += outputError[i+1][k] * weight[i][j][k];
                }
                
                outputError[i][j] *= prevError;
                
                
                //입력층과 은닉층 사이의 가중치 보정
                if(i==2)
                {
                    for(int prev=1;prev<=numOfInputLayerNode;prev++)
                        weight[i-1][prev][j] += learningRate * outputError[i][j] * nodeOutput[i-1][prev];
                }
                
                //은닉층과 은닉층 사이의 가중치 보정
                else
                {
                    for(int prev=1;prev<=numOfHiddenLayerNode;prev++)
                        weight[i-1][prev][j] += learningRate * outputError[i][j] * nodeOutput[i-1][prev];
                }
            }
        }
    }
};


int main(int argc, const char * argv[])
{
    
    int numOfTrainingInput=8;
    int numOfTrainingOutput=8;
    
    int numOfInputLayerNode=28*28;
    int numOfHiddenLayer=2;
    int numOfHiddenLayerNode=40;
    int numOfOutputLayerNode=10;
    double learningRate = 0.5;
    
    
    //epoch (반복횟수) 설정
    int epoch = 500;
    
    /*
     vector< vector<double> >input(numOfTrainingInput+1);
     for(int i=1;i<=numOfTrainingInput;i++)
     {
     input[i].resize(numOfInputLayerNode+1);
     }
     
     
     // 3개 비트의 XOR
     input[1][1]=0;
     input[1][2]=0;
     input[1][3]=0;
     
     
     input[2][1]=0;
     input[2][2]=0;
     input[2][3]=1;
     
     
     input[3][1]=0;
     input[3][2]=1;
     input[3][3]=0;
     
     input[4][1]=0;
     input[4][2]=1;
     input[4][3]=1;
     
     input[5][1]=1;
     input[5][2]=0;
     input[5][3]=0;
     
     input[6][1]=1;
     input[6][2]=0;
     input[6][3]=1;
     
     input[7][1]=1;
     input[7][2]=1;
     input[7][3]=0;
     
     input[8][1]=1;
     input[8][2]=1;
     input[8][3]=1;
     
     
     vector< vector<double> >output(numOfTrainingOutput+1);
     for(int i=1;i<=numOfTrainingOutput;i++)
     output[i].resize(numOfOutputLayerNode+1);
     
     
     output[1][1]=0;
     output[2][1]=1;
     output[3][1]=1;
     output[4][1]=0;
     output[5][1]=1;
     output[6][1]=0;
     output[7][1]=0;
     output[8][1]=1;
     
     
     
     */
    Network* net = new Network(numOfInputLayerNode,numOfHiddenLayer,numOfHiddenLayerNode,numOfOutputLayerNode,learningRate);
    
    net->init();
    
    /*
     for(int k=0;k<=epoch;k++)
     {
     for(int i=1;i<=numOfTrainingInput;i++)
     {
     net->backPropagation(input[i], output[i]);
     }
     
     
     if(k%10000==0)
     {
     printf("learning %d times...\n",k);
     for(int j=1;j<=numOfTrainingInput;j++)
     {
     net->activate(input[j]);
     
     printf("input : ");
     for(int k=1;k<=numOfInputLayerNode;k++)
     printf("%.1lf ",input[j][k]);
     printf("  output : ");
     for(int k=1;k<=numOfOutputLayerNode;k++)
     printf("%lf ",net->nodeOutput[net->numOfLayer][k]);
     printf("\n");
     
     }
     printf("******************\n");
     }
     }
     */
    
    FILE* fp = fopen("train.csv","r");
    
    vector<double> in(numOfInputLayerNode+1);
    vector<double> out(numOfOutputLayerNode+1,0);
    
    time_t startTime = time(NULL);
    time_t currentTime;
    for(int t=1;t<=epoch;t++)
    {
        fclose(fp);
        fp = fopen("train.csv","r");
        for(int k=1;k<=30000;k++)
        {
            in.clear();
            out.clear();
            
            in.resize(numOfInputLayerNode+1);
            out.resize(numOfOutputLayerNode+1,0);
            
            
            if(k%100==0)
            {
                currentTime = time(NULL);
                printf("%d epoch, %d learning, %ld secs passed...\n",t,k,
                       currentTime-startTime);
                
                
            }
            int label;
            char temp;
            int color;
            
            
            fscanf(fp,"%d",&label);
            
            out[label+1]=1;
            
            fscanf(fp,"%c",&temp);
            
            for(int i=0;i<28;i++)
            {
                for(int j=0;j<28;j++)
                {
                    fscanf(fp,"%d",&color);
                    fscanf(fp,"%c",&temp);
                    
                    if(color<0 || color>255)
                    {
                        printf("color error\n");
                        return 0;
                    }
                    
                    if(color == 0)
                        in[i*28+j+1]=0;
                    else
                        in[i*28+j+1]=1;
                }
            }
            
            fscanf(fp,"%c",&temp);
            if(temp!=10)
            {
                printf("error\n");
                printf("%c\n",temp);
                
            }
            
            
            net->backPropagation(in, out);
            
            
            
        }
    }
    
    
    double cnt=0;
    double total=5000;
    
    
    for(int k=1;k<=5000;k++)
    {
        in.clear();
        out.clear();
        
        in.resize(numOfInputLayerNode+1);
        out.resize(numOfOutputLayerNode+1,0);
        
        if(k%100==0)
        {
            printf("%d testing...\n",k);
            printf("current cnt is...%d\n",int(cnt));
        }
        int label;
        char temp;
        int color;
        
        
        fscanf(fp,"%d",&label);
        
        out[label+1]=1;
        
        fscanf(fp,"%c",&temp);
        
        for(int i=0;i<28;i++)
        {
            for(int j=0;j<28;j++)
            {
                fscanf(fp,"%d",&color);
                fscanf(fp,"%c",&temp);
                
                if(color<0 || color>255)
                    printf("color error\n");
                
                if(color==0)
                    in[i*28+j+1]=0;
                else
                    in[i*28+j+1]=1;
                
                //in[i*28+j+1]=color;
            }
        }
        
        fscanf(fp,"%c",&temp);
        if(temp!=10 && temp!=0)
        {
            printf("error\n");
            printf("%c\n",temp);
            
        }
        net->activate(in);
        
        double maxV=-987654321;
        int ans=-1;
        
        for(int i=1;i<=numOfOutputLayerNode;i++)
        {
            if(net->nodeOutput[net->numOfLayer][i] > maxV)
            {
                maxV = max(maxV,net->nodeOutput[net->numOfLayer][i]);
                ans=i-1;
            }
        }
        
        if(label == ans)
            cnt+=1;
        
    }
    
    double correctRate = cnt/total;
    correctRate*=100;
    fclose(fp);
    printf("FINAL CORRECT RATE : %lf\n",correctRate);
    printf("NUM OF HIDDEN LAYER : %d\n",numOfHiddenLayer);
    printf("NUM OF HIDDEN LAYER NODES : %d\n",numOfHiddenLayerNode);
    
}